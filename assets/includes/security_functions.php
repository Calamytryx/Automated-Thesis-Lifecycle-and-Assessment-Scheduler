<?php

function _cleaninjections($test) {

    $find = array(
        "/[\r\n]/", 
        "/%0[A-B]/",
        "/%0[a-b]/",
        "/bcc\:/i",
        "/Content\-Type\:/i",
        "/Mime\-Version\:/i",
        "/cc\:/i",
        "/from\:/i",
        "/to\:/i",
        "/Content\-Transfer\-Encoding\:/i"
    );
    $ret = preg_replace($find, "", $test);
    return $ret;
}

function generate_csrf_token() {

    if (!isset($_SESSION)) {

        session_start();
    }

    if (empty($_SESSION['token'])) {

        $_SESSION['token'] = bin2hex(random_bytes(32));
    }
}

function insert_csrf_token() {

    generate_csrf_token();

    echo '<input type="hidden" name="token" value="' . $_SESSION['token'] . '" />';
}

function sanitize_html_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_html_input', $data);
    }
    
    if (empty($data)) return $data;
    
    // Strip all HTML tags and decode HTML entities to plain text
    $stripped = strip_tags($data);
    $decoded = html_entity_decode($stripped, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    // Remove emojis and other Unicode symbols - comprehensive pattern
    // This covers all major emoji blocks including complex sequences with ZWJ and variation selectors
    $cleaned = preg_replace('/[\x{1F600}-\x{1F64F}]|[\x{1F300}-\x{1F5FF}]|[\x{1F680}-\x{1F6FF}]|[\x{1F1E0}-\x{1F1FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}]|[\x{1F900}-\x{1F9FF}]|[\x{1F018}-\x{1F270}]|[\x{238C}-\x{2454}]|[\x{20D0}-\x{20FF}]|[\x{FE00}-\x{FE0F}]|[\x{1F170}-\x{1F251}]|[\x{1F004}]|[\x{1F0CF}]|[\x{1F18E}]|[\x{3030}]|[\x{2B50}]|[\x{2B55}]|[\x{2934}-\x{2935}]|[\x{2B05}-\x{2B07}]|[\x{2B1B}-\x{2B1C}]|[\x{3297}]|[\x{3299}]|[\x{303D}]|[\x{00A9}]|[\x{00AE}]|[\x{2122}]|[\x{23E9}-\x{23FA}]|[\x{25AA}-\x{25AB}]|[\x{25B6}]|[\x{25C0}]|[\x{25FB}-\x{25FE}]|[\x{2600}-\x{2604}]|[\x{260E}]|[\x{2611}]|[\x{2614}-\x{2615}]|[\x{2618}]|[\x{261D}]|[\x{2620}]|[\x{2622}-\x{2623}]|[\x{2626}]|[\x{262A}]|[\x{262E}-\x{262F}]|[\x{2638}-\x{263A}]|[\x{2640}]|[\x{2642}]|[\x{2648}-\x{2653}]|[\x{2660}]|[\x{2663}]|[\x{2665}-\x{2666}]|[\x{2668}]|[\x{267B}]|[\x{267E}-\x{267F}]|[\x{2692}-\x{2697}]|[\x{2699}]|[\x{269B}-\x{269C}]|[\x{26A0}-\x{26A1}]|[\x{26AA}-\x{26AB}]|[\x{26B0}-\x{26B1}]|[\x{26BD}-\x{26BE}]|[\x{26C4}-\x{26C5}]|[\x{26C8}]|[\x{26CE}-\x{26CF}]|[\x{26D1}]|[\x{26D3}-\x{26D4}]|[\x{26E9}-\x{26EA}]|[\x{26F0}-\x{26F5}]|[\x{26F7}-\x{26FA}]|[\x{26FD}]|[\x{2702}]|[\x{2705}]|[\x{2708}-\x{270D}]|[\x{270F}]|[\x{2712}]|[\x{2714}]|[\x{2716}]|[\x{271D}]|[\x{2721}]|[\x{2728}]|[\x{2733}-\x{2734}]|[\x{2744}]|[\x{2747}]|[\x{274C}]|[\x{274E}]|[\x{2753}-\x{2755}]|[\x{2757}]|[\x{2763}-\x{2764}]|[\x{2795}-\x{2797}]|[\x{27A1}]|[\x{27B0}]|[\x{27BF}]|[\x{2934}-\x{2935}]|[\x{200D}]|[\x{1F000}-\x{1F02F}]|[\x{1F0A0}-\x{1F0FF}]|[\x{1F100}-\x{1F64F}]|[\x{1F680}-\x{1F6FF}]|[\x{1F700}-\x{1F77F}]|[\x{1F780}-\x{1F7FF}]|[\x{1F800}-\x{1F8FF}]|[\x{1F900}-\x{1F9FF}]|[\x{1FA00}-\x{1FA6F}]|[\x{1FA70}-\x{1FAFF}]/u', '', $decoded);
    
    // Remove any remaining non-printable characters except newlines and tabs
    $final = preg_replace('/[^\P{C}\n\r\t]/u', '', $cleaned);
    
    return trim($final);
}

function verify_csrf_token() {

    generate_csrf_token();

    if (!empty($_POST['token'])) {

        if (hash_equals($_SESSION['token'], $_POST['token'])) {

            return true;
        } 
        else {
            
            return false;
        }
    }
    else {

        return false;
    }
}