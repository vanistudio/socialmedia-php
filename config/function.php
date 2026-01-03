<?php $Vani = new Vani;
function check_string($data)
{
    return htmlspecialchars(addslashes(str_replace(' ', '', $data)));
}
function check_string2($data)
{
    return (trim(htmlspecialchars(addslashes($data))));
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function get_csrf_token() {
    return $_SESSION['csrf_token'] ?? generate_csrf_token();
}

function validate_csrf_token($token) {
    if (!isset($_SESSION['csrf_token'])) {
        generate_csrf_token();
    }
    if (empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function csrf_token_input() {
    $token = get_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

use Pusher\Pusher;

function getPusher() {
    $appId = $_ENV['PUSHER_APP_ID'] ?? '';
    $key = $_ENV['PUSHER_APP_KEY'] ?? '';
    $secret = $_ENV['PUSHER_APP_SECRET'] ?? '';
    $cluster = $_ENV['PUSHER_APP_CLUSTER'] ?? 'ap1';
    $useTLS = ($_ENV['PUSHER_USE_TLS'] ?? 'true') === 'true';
    
    if (empty($appId) || empty($key) || empty($secret)) {
        return null;
    }
    
    try {
        return new Pusher(
            $key,
            $secret,
            $appId,
            [
                'cluster' => $cluster,
                'useTLS' => $useTLS,
            ]
        );
    } catch (Exception $e) {
        error_log('Pusher initialization error: ' . $e->getMessage());
        return null;
    }
}

use OpenAI\Client;

function getOpenAIClient() {
    $apiKey = $_ENV['OPENAI_API_KEY'] ?? '';
    if (empty($apiKey) || $apiKey === 'your_openai_api_key_here') {
        return null;
    }
    
    try {
        return \OpenAI::client($apiKey);
    } catch (Exception $e) {
        error_log('OpenAI initialization error: ' . $e->getMessage());
        return null;
    }
}

function normalizeText($text) {
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);
    return $text;
}

function checkBlacklist($text) {
    if (empty($text)) {
        return ['flagged' => false, 'keywords' => []];
    }
    
    global $Vani;
    if (!isset($Vani) || !$Vani) {
        return ['flagged' => false, 'keywords' => []];
    }
    
    try {
        $blacklist = $Vani->get_list("SELECT keyword FROM blacklist_keywords WHERE active = 1");
        $foundKeywords = [];
        $textNormalized = normalizeText($text);
        
        foreach ($blacklist as $item) {
            $keyword = mb_strtolower(trim($item['keyword'] ?? ''), 'UTF-8');
            if (empty($keyword)) continue;
            
            $keywordNormalized = normalizeText($keyword);
            
            if (mb_strpos($textNormalized, $keywordNormalized) !== false) {
                $foundKeywords[] = $keyword;
            }
            
            $keywordNoSpaces = str_replace(' ', '', $keywordNormalized);
            $textNoSpaces = str_replace(' ', '', $textNormalized);
            if (!empty($keywordNoSpaces) && mb_strpos($textNoSpaces, $keywordNoSpaces) !== false) {
                if (!in_array($keyword, $foundKeywords)) {
                    $foundKeywords[] = $keyword;
                }
            }
        }
        
        return [
            'flagged' => !empty($foundKeywords),
            'keywords' => array_unique($foundKeywords),
        ];
    } catch (Exception $e) {
        error_log('Blacklist check error: ' . $e->getMessage());
        return ['flagged' => false, 'keywords' => []];
    }
}

function moderateContent($text) {
    if (empty($text)) {
        return ['flagged' => false, 'violations' => [], 'source' => 'none'];
    }
    
    $enabled = ($_ENV['CONTENT_MODERATION_ENABLED'] ?? 'true') === 'true';
    if (!$enabled) {
        return ['flagged' => false, 'violations' => [], 'source' => 'disabled'];
    }
    
    $violations = [];
    $scores = [];
    $source = 'none';
    
    $blacklistCheck = checkBlacklist($text);
    if ($blacklistCheck['flagged']) {
        $violations[] = 'từ khóa cấm';
        $source = 'blacklist';
    }
    
    $client = getOpenAIClient();
    if ($client) {
        try {
            $response = $client->moderations()->create([
                'model' => 'text-moderation-latest',
                'input' => $text,
            ]);
            
            $result = $response->toArray();
            $flagged = $result['results'][0]['flagged'] ?? false;
            $categories = $result['results'][0]['categories'] ?? [];
            $categoryScores = $result['results'][0]['category_scores'] ?? [];
            
            if ($flagged) {
                if ($source === 'none') {
                    $source = 'openai';
                } else {
                    $source = 'both';
                }
                $scores = $categoryScores;
                
                if ($categories['violence'] ?? false) {
                    $violations[] = 'bạo lực';
                }
                if ($categories['harassment'] ?? false) {
                    $violations[] = 'quấy rối';
                }
                if ($categories['hate'] ?? false) {
                    $violations[] = 'xúc phạm';
                }
                if ($categories['self-harm'] ?? false) {
                    $violations[] = 'tự hại';
                }
                if ($categories['sexual'] ?? false) {
                    $violations[] = 'nội dung tình dục';
                }
                if ($categories['sexual/minors'] ?? false) {
                    $violations[] = 'nội dung tình dục trẻ em';
                }
                if ($categories['hate/threatening'] ?? false) {
                    $violations[] = 'đe dọa';
                }
                if ($categories['violence/graphic'] ?? false) {
                    $violations[] = 'bạo lực hình ảnh';
                }
            }
        } catch (Exception $e) {
            error_log('OpenAI moderation error: ' . $e->getMessage());
        }
    }
    
    $isFlagged = !empty($violations);
    
    return [
        'flagged' => $isFlagged,
        'violations' => array_unique($violations),
        'scores' => $scores,
        'blacklist_keywords' => $blacklistCheck['keywords'],
        'source' => $source,
    ];
}

function logContentModeration($userId, $contentType, $content, $moderationResult, $action = 'blocked', $relatedId = null) {
    global $Vani;
    
    if (!isset($Vani) || !$Vani) {
        return;
    }
    
    try {
        $Vani->insert('content_moderation_logs', [
            'user_id' => intval($userId),
            'content_type' => $contentType,
            'content' => $content,
            'related_id' => $relatedId ? intval($relatedId) : null,
            'violations' => json_encode($moderationResult['violations'] ?? []),
            'blacklist_keywords' => json_encode($moderationResult['blacklist_keywords'] ?? []),
            'scores' => json_encode($moderationResult['scores'] ?? []),
            'source' => $moderationResult['source'] ?? 'none',
            'action' => $action,
        ]);
    } catch (Exception $e) {
        error_log('Failed to log content moderation: ' . $e->getMessage());
    }
}