<?php $Vani = new Vani;
function check_string($data)
{
    return htmlspecialchars(addslashes(str_replace(' ', '', $data)));
}
function check_string2($data)
{
    return (trim(htmlspecialchars(addslashes($data))));
}