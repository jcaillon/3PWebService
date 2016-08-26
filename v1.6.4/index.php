<?php
/**
 * Created by PhpStorm.
 * User: Julien
 * Date: 26/04/2015
 * Time: 14:53
 */

include_once("SPDO.php");
http_response_code(200);

if (!isset($_GET['action'])) {
    die();
} else {
    $action = htmlspecialchars($_GET['action']);
}

if (!isset($_GET['softName'])) {
    die();
} else {
    $softName = htmlspecialchars($_GET['softName']);
}

SPDO::getInstance()->beginTransaction();

switch ($action) {


    // ------------------------------
    // Ping
    // ------------------------------
    case 'ping':

        $json = file_get_contents_utf8('php://input');
        $input = json_decode($json);

        if (!isset($input->{'UUID'})) {
            rollbackAndDie();
        }

        $req = SPDO::getInstance()->prepare('SELECT location FROM MySoft_ping WHERE UUID = :UUID AND softName = :softName');
        $req->execute(array(
            'UUID' => $input->{'UUID'},
            'softName' => $softName
        ));
        $user = $req->fetch();

        // default response
        $json = array("status" => 0, "msg" => "Fail!");

        if (count($user) > 1) {

            // update

            // we got the location or just an ip?
            $ip = $user['location'];
            if (startsWithNumber($ip))
                $ip = get_user_location();

            $req = SPDO::getInstance()->prepare('UPDATE MySoft_ping SET nbPing = nbPing + 1, lastPing = NOW(), userName = :userName, version = :version, location = :location WHERE UUID = :UUID AND softName = :softName');
            $req->execute(array(
                'userName' => $input->{'userName'},
                'version' => $input->{'version'},
                'UUID' => $input->{'UUID'},
                'softName' => $softName,
                'location' => $ip
            ));
            if ($req->rowCount() >= 1) {
                $json = array("status" => 1, "msg" => "User updated");
            }

        } else {

            // insert

            // get user location through ip
            $ip = get_user_location();

            $req = SPDO::getInstance()->prepare('INSERT INTO MySoft_ping(UUID, softName, userName, firstPing, lastPing, location, version) VALUES(:UUID, :softName, :userName, NOW(), NOW(), :location, :version)');
            if ($req->execute(array(
                    'UUID' => $input->{'UUID'},
                    'softName' => $softName,
                    'userName' => $input->{'userName'},
                    'location' => $ip,
                    'version' => $input->{'version'}
                ))) {
                $id = SPDO::getInstance()->lastInsertId();
                $json = array("status" => 1, "msg" => "New user, id = " . $id);
            }
        }

        header('Content-type: application/json');
        echo json_encode($json, JSON_PRETTY_PRINT);
        break;


    // ------------------------------
    // Get ping
    // ------------------------------
    case 'getPing':

        $req = SPDO::getInstance()->prepare('SELECT UUID, userName, firstPing, lastPing, location, version, nbPing FROM MySoft_ping ORDER BY lastPing DESC');
        $req->execute();

        $users = array();
        while ($user = $req->fetch()) {
            $users[] = array(
                'UUID'=>$user['UUID'],
                'userName'=>$user['userName'],
                'firstPing'=>$user['firstPing'],
                'lastPing'=>$user['lastPing'],
                'location' => $user['location'],
                'version' => $user['version'],
                'nbPing' => $user['nbPing']
            );
        }

        header('Content-type: application/json');
        echo json_encode($users, JSON_PRETTY_PRINT);
        break;


    // ------------------------------
    // Bugs
    // ------------------------------
    case 'bugs':

        $json = file_get_contents_utf8('php://input');
        $input = json_decode($json);

        if (!isset($input->{'UUID'})) {
            rollbackAndDie();
        }

        $req = SPDO::getInstance()->prepare('SELECT UUID FROM MySoft_bugs WHERE softName = :softName AND originVersion = :originVersion AND originMethod = :originMethod AND originLine = :originLine');
        $req->execute(array(
            'softName' => $softName,
            'originVersion' => $input->{'originVersion'},
            'originMethod' => $input->{'originMethod'},
            'originLine' => $input->{'originLine'}
        ));

        // default response
        $json = array("status" => 0, "msg" => "Fail!");

        if (count($req->fetch()) > 1) {

            // update

            $req = SPDO::getInstance()->prepare('UPDATE MySoft_bugs SET nbReceived = nbReceived + 1, receptionTime = NOW() WHERE softName = :softName AND originVersion = :originVersion AND originMethod = :originMethod AND originLine = :originLine');
            $req->execute(array(
                'softName' => $softName,
                'originVersion' => $input->{'originVersion'},
                'originMethod' => $input->{'originMethod'},
                'originLine' => $input->{'originLine'}
            ));
            if ($req->rowCount() >= 1) {
                $json = array("status" => 1, "msg" => "Bug updated");
            }

        } else {

            // insert

            $req = SPDO::getInstance()->prepare('INSERT INTO MySoft_bugs(softName, originVersion, originMethod, originLine, receptionTime, UUID, message, fullException) VALUES(:softName, :originVersion, :originMethod, :originLine, NOW(), :UUID, :message, :fullException)');
            if ($req->execute(array(
                'softName' => $softName,
                'originVersion' => $input->{'originVersion'},
                'originMethod' => $input->{'originMethod'},
                'originLine' => $input->{'originLine'},
                'UUID' => $input->{'UUID'},
                'message' => $input->{'message'},
                'fullException' => $input->{'fullException'}
            ))) {
                $id = SPDO::getInstance()->lastInsertId();
                $json = array("status" => 1, "msg" => "New bug, id = " . $id);
            }
        }

        header('Content-type: application/json');
        echo json_encode($json, JSON_PRETTY_PRINT);
        break;



    // ------------------------------
    // Get Bugs
    // ------------------------------
    case 'getBugs':

        $req = SPDO::getInstance()->prepare('SELECT originVersion, originMethod, originLine, receptionTime, nbReceived, UUID, message, fullException FROM MySoft_bugs ORDER BY receptionTime DESC');
        $req->execute();

        $users = array();
        while ($user = $req->fetch()) {
            $users[] = array(
                'originVersion'=>$user['originVersion'],
                'originMethod'=>$user['originMethod'],
                'originLine'=>$user['originLine'],
                'message' => $user['message'],
                'fullException' => $user['fullException'],
                'UUID' => $user['UUID'],
                'receptionTime' => $user['receptionTime'],
                'nbReceived' => $user['nbReceived'],
            );
        }

        header('Content-type: application/json');
        echo json_encode($users, JSON_PRETTY_PRINT);
        break;

    default:
        rollbackAndDie();
        break;
}

try {
    SPDO::getInstance()->commit();
} finally {
    http_response_code(200);
}

function rollbackAndDie() {
    SPDO::getInstance()->rollBack();
    die();
}

function file_get_contents_utf8($fn) {
    $ctx = stream_context_create(array(
            'http' => array(
                'timeout' => 15
            )
        )
    );
    $content = file_get_contents($fn, false, $ctx);
    return mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
}

function get_user_location() {
    // get user location through ip
    $ip = $_SERVER['REMOTE_ADDR'];
    $location = json_decode(file_get_contents_utf8("http://ipinfo.io/{$ip}/json"));
    if (!empty($location->country))
        $ip = $location->country;
    if (!empty($location->region))
        $ip = $ip . ", " . $location->region;
    if (!empty($location->city))
        $ip = $ip . ", " . $location->city;
    return $ip;
}

function startsWithNumber($string) {
    return strlen($string) > 0 && ctype_digit(substr($string, 0, 1));
}