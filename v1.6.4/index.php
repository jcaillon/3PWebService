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

        $json = file_get_contents('php://input');
        $input = json_decode($json);

        if (!isset($input->{'UUID'})) {
            rollbackAndDie();
        }

        $req = SPDO::getInstance()->prepare('SELECT UUID FROM MySoft_ping WHERE UUID = :UUID AND softName = :softName');
        $req->execute(array(
            'UUID' => $input->{'UUID'},
            'softName' => $softName
        ));
        $user = $req->fetch();

        // default response
        $json = array("status" => 0, "msg" => "Nop!");

        if (count($user) > 1) {

            // update

            $req = SPDO::getInstance()->prepare('UPDATE MySoft_ping SET nbPing = nbPing + 1, lastPing = NOW(), userName = :userName, lang = :lang, version = :version WHERE UUID = :UUID AND softName = :softName');
            $req->execute(array(
                'userName' => $input->{'userName'},
                'lang' => $input->{'lang'},
                'version' => $input->{'version'},
                'UUID' => $input->{'UUID'},
                'softName' => $softName
            ));
            if ($req->rowCount() >= 1) {
                $json = array("status" => 1, "msg" => "User updated");
            }

        } else {

            // insert

            $req = SPDO::getInstance()->prepare('INSERT INTO MySoft_ping(UUID, softName, userName, firstPing, lastPing, lang, version) VALUES(:UUID, :softName, :userName, NOW(), NOW(), :lang, :version)');
            if ($req->execute(array(
                    'UUID' => $input->{'UUID'},
                    'softName' => $softName,
                    'userName' => $input->{'userName'},
                    'lang' => $input->{'lang'},
                    'version' => $input->{'version'}
                ))) {
                $id = SPDO::getInstance()->lastInsertId();
                $json = array("status" => 1, "msg" => $id);
            }
        }

        header('Content-type: application/json');
        echo json_encode($json, JSON_PRETTY_PRINT);
        break;


    // ------------------------------
    // Get ping
    // ------------------------------
    case 'getPing':

        $req = SPDO::getInstance()->prepare('SELECT UUID, userName, firstPing, lastPing, lang, version FROM MySoft_ping ORDER BY lastPing DESC');
        $req->execute();

        $users = array();
        while ($user = $req->fetch()) {
            $users[] = array(
                'UUID'=>$user['UUID'],
                'userName'=>$user['userName'],
                'firstPing'=>$user['firstPing'],
                'lastPing'=>$user['lastPing'],
                'lang' => $user['lang'],
                'version' => $user['version']
            );
        }

        header('Content-type: application/json');
        echo json_encode($users, JSON_PRETTY_PRINT);
        break;


    // ------------------------------
    // Bugs
    // ------------------------------
    case 'bugs':

        $json = file_get_contents('php://input');
        $input = json_decode($json);

        if (!isset($input->{'UUID'})) {
            rollbackAndDie();
        }

        $req = SPDO::getInstance()->prepare('SELECT UUID FROM MySoft_bugs WHERE softName = :softName AND originVersion = :originVersion AND originClass = :originClass AND originLine = :originLine');
        $req->execute(array(
            'softName' => $softName,
            'originVersion' => $input->{'originVersion'},
            'originClass' => $input->{'originClass'},
            'originLine' => $input->{'originLine'}
        ));

        // default response
        $json = array("status" => 0, "msg" => "Nop!");

        if (count($req->fetch()) > 1) {

            // update

            $req = SPDO::getInstance()->prepare('UPDATE MySoft_bugs SET nbReceived = nbReceived + 1 WHERE softName = :softName AND originVersion = :originVersion AND originClass = :originClass AND originLine = :originLine');
            $req->execute(array(
                'softName' => $softName,
                'originVersion' => $input->{'originVersion'},
                'originClass' => $input->{'originClass'},
                'originLine' => $input->{'originLine'}
            ));
            if ($req->rowCount() >= 1) {
                $json = array("status" => 1, "msg" => "User updated");
            }

        } else {

            // insert

            $req = SPDO::getInstance()->prepare('INSERT INTO MySoft_bugs(softName, originVersion, originClass, originLine, receptionTime, UUID, message, fullException) VALUES(:softName, :originVersion, :originClass, :originLine, NOW(), :UUID, :message, :fullException)');
            if ($req->execute(array(
                'softName' => $softName,
                'originVersion' => $input->{'originVersion'},
                'originClass' => $input->{'originClass'},
                'originLine' => $input->{'originLine'},
                'UUID' => $input->{'UUID'},
                'message' => $input->{'message'},
                'fullException' => $input->{'fullException'}
            ))) {
                $id = SPDO::getInstance()->lastInsertId();
                $json = array("status" => 1, "msg" => $id);
            }
        }

        header('Content-type: application/json');
        echo json_encode($json, JSON_PRETTY_PRINT);
        break;



    // ------------------------------
    // Get Bugs
    // ------------------------------
    case 'getBugs':

        $req = SPDO::getInstance()->prepare('SELECT originVersion, originClass, originLine, receptionTime, nbReceived, UUID, message, fullException FROM MySoft_bugs ORDER BY receptionTime DESC');
        $req->execute();

        $users = array();
        while ($user = $req->fetch()) {
            $users[] = array(
                'originVersion'=>$user['originVersion'],
                'originClass'=>$user['originClass'],
                'originLine'=>$user['originLine'],
                'receptionTime' => $user['receptionTime'],
                'nbReceived' => $user['nbReceived'],
                'UUID' => $user['UUID'],
                'message' => $user['message'],
                'fullException' => $user['fullException']
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