<?php
/**
 * Created by PhpStorm.
 * User: Julien
 * Date: 26/04/2015
 * Time: 14:53
 */

include_once("SPDO.php");
//http_response_code(500);
http_response_code(200);

if (!isset($_GET['action'])) {
    $action = 'get';
} else {
    $action = htmlspecialchars($_GET['action']);
}

SPDO::getInstance()->beginTransaction();

switch ($action) {

    // ------------------------------
    // Ping
    // ------------------------------
    case 'ping':

        $json = file_get_contents('php://input');
        $input = json_decode($json);

        $req = SPDO::getInstance()->prepare('SELECT userName FROM 3pusers WHERE computerId = :computerId');
        $req->execute(array(
            'computerId' => $input->{'computerId'}
        ));
        $user = $req->fetch();

        // default response
        $json = array("status" => 0, "msg" => "Herp derp!");

        if (count($user) > 1) {

            // update

            $req = SPDO::getInstance()->prepare('UPDATE 3pusers SET nbPing = nbPing + 1, lastUpdateTime = NOW(), 3pVersion = :3pVersion, NppVersion = :NppVersion, timeZone = :timeZone, lang = :lang WHERE computerId = :computerId');
            $req->execute(array(
                '3pVersion' => $input->{'3pVersion'},
                'NppVersion' => $input->{'NppVersion'},
                'timeZone' => $input->{'timeZone'},
                'lang' => $input->{'lang'},
                'computerId' => $input->{'computerId'},
            ));
            if ($req->rowCount() >= 1) {
                $json = array("status" => 1, "msg" => "User updated");
            }

        } else {

            // insert

            $req = SPDO::getInstance()->prepare('INSERT INTO 3pusers(createTime, lastUpdateTime, computerId, userName, 3pVersion, NppVersion, timeZone, lang) VALUES(NOW(), NOW(), :computerId, :userName, :3pVersion, :NppVersion, :timeZone, :lang)');
            if ($req->execute(array(
                    'computerId' => $input->{'computerId'},
                    'userName' => $input->{'userName'},
                    '3pVersion' => isset($input->{'3pVersion'}) ? $input->{'3pVersion'} : "",
                    'NppVersion' => isset($input->{'NppVersion'}) ? $input->{'NppVersion'} : "",
                    'timeZone' => isset($input->{'timeZone'}) ? $input->{'timeZone'} : "",
                    'lang' => isset($input->{'lang'}) ? $input->{'lang'} : "",
                ))) {
                $id = SPDO::getInstance()->lastInsertId();
                $json = array("status" => 1, "msg" => $id);
            }
        }

        header('Content-type: application/json');
        echo json_encode($json, JSON_PRETTY_PRINT);
        break;


    // ------------------------------
    // Get
    // ------------------------------
    case 'get':

        $req = SPDO::getInstance()->prepare('SELECT userName, nbPing, createTime, lastUpdateTime, 3pVersion, NppVersion, timeZone, lang FROM 3pusers');
        $req->execute();

        $users = array();
        while ($user = $req->fetch()) {
            $users[] = array(
                'userName'=>$user['userName'],
                'nbPing'=>$user['nbPing'],
                'createTime'=>$user['createTime'],
                'lastUpdateTime'=>$user['lastUpdateTime'],
                '3pVersion' => $user['3pVersion'],
                'NppVersion' => $user['NppVersion'],
                'timeZone' => $user['timeZone'],
                'lang' => $user['lang'],
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