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

        $req = SPDO::getInstance()->prepare('SELECT UUID, userName, firstPing, lastPing, location, version, nbPing FROM MySoft_ping WHERE softName = :softName ORDER BY lastPing DESC');
        $req->execute(array('softName' => $softName));

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

        $req = SPDO::getInstance()->prepare('SELECT originVersion, originMethod, originLine, receptionTime, nbReceived, UUID, message, fullException FROM MySoft_bugs WHERE softName = :softName ORDER BY receptionTime DESC');
        $req->execute(array('softName' => $softName));

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



    // ------------------------------
    // Get Recap
    // ------------------------------
    case 'getRecap':

        $recap = array();

        $req = SPDO::getInstance()->prepare('SELECT location FROM MySoft_ping WHERE softName = :softName');
        $req->execute(array('softName' => $softName));
        $user = $req->fetch();


        $req = SPDO::getInstance()->prepare('SELECT COUNT(1) AS nbUsers FROM MySoft_ping WHERE softName = :softName');
        $req->execute(array('softName' => $softName));
        $res = $req->fetch();
        $recap['totalUsers'] = $res['nbUsers'];

        $req = SPDO::getInstance()->prepare('SELECT COUNT(1) AS nbUsers FROM MySoft_ping WHERE softName = :softName AND DATE(lastPing) BETWEEN DATE_SUB(DATE(NOW()), INTERVAL 1 DAY) AND CURDATE()');
        $req->execute(array('softName' => $softName));
        $res = $req->fetch();
        $recap['dayUsers'] = $res['nbUsers'];

        $req = SPDO::getInstance()->prepare('SELECT COUNT(1) AS nbUsers FROM MySoft_ping WHERE softName = :softName AND DATE(lastPing) BETWEEN DATE_SUB(DATE(NOW()), INTERVAL 6 DAY) AND CURDATE()');
        $req->execute(array('softName' => $softName));
        $res = $req->fetch();
        $recap['weeklyUsers'] = $res['nbUsers'];

        $req = SPDO::getInstance()->prepare('SELECT version, COUNT(*) AS nbUsers FROM MySoft_ping WHERE softName = :softName GROUP BY version');
        $req->execute(array('softName' => $softName));
        $recap['usersByVersion'] = $req->fetchAll(PDO::FETCH_ASSOC);

        $req = SPDO::getInstance()->prepare('SELECT SUBSTR(location, 1, 2) AS country, COUNT(*) AS nbUsers FROM MySoft_ping WHERE softName = :softName AND concat("", SUBSTR(location, 1, 1) * 1) <> SUBSTR(location, 1, 1) GROUP BY SUBSTR(location, 1, 2) ORDER BY nbusers DESC');
        $req->execute(array('softName' => $softName));
        $usersByCountry = $req->fetchAll(PDO::FETCH_ASSOC);

        $countryCode = array(
            "AF"=>"Afghanistan", "AX"=>"Aland Islands", "AL"=>"Albania", "DZ"=>"Algeria", "AS"=>"American Samoa", "AD"=>"Andorra", "AO"=>"Angola", "AI"=>"Anguilla", "AQ"=>"Antarctica", "AG"=>"Antigua and Barbuda", "AR"=>"Argentina", "AM"=>"Armenia", "AW"=>"Aruba", "AU"=>"Australia", "AT"=>"Austria", "AZ"=>"Azerbaijan", "BS"=>"Bahamas", "BH"=>"Bahrain", "BD"=>"Bangladesh", "BB"=>"Barbados", "BY"=>"Belarus", "BE"=>"Belgium", "BZ"=>"Belize", "BJ"=>"Benin", "BM"=>"Bermuda", "BT"=>"Bhutan", "BO"=>"Bolivia", "BA"=>"Bosnia and Herzegovina", "BW"=>"Botswana", "BV"=>"Bouvet Island", "BR"=>"Brazil", "VG"=>"British Virgin Islands", "IO"=>"British Indian Ocean Territory", "BN"=>"Brunei Darussalam", "BG"=>"Bulgaria", "BF"=>"Burkina Faso", "BI"=>"Burundi", "KH"=>"Cambodia", "CM"=>"Cameroon", "CA"=>"Canada", "CV"=>"Cape Verde", "KY"=>"Cayman Islands", "CF"=>"Central African Republic", "TD"=>"Chad", "CL"=>"Chile", "CN"=>"China", "HK"=>"Hong Kong, SAR China", "MO"=>"Macao, SAR China", "CX"=>"Christmas Island", "CC"=>"Cocos (Keeling) Islands", "CO"=>"Colombia", "KM"=>"Comoros", "CG"=>"Congo (Brazzaville)", "CD"=>"Congo, (Kinshasa)", "CK"=>"Cook Islands", "CR"=>"Costa Rica", "CI"=>"Côte d'Ivoire", "HR"=>"Croatia", "CU"=>"Cuba", "CY"=>"Cyprus", "CZ"=>"Czech Republic", "DK"=>"Denmark", "DJ"=>"Djibouti", "DM"=>"Dominica", "DO"=>"Dominican Republic", "EC"=>"Ecuador", "EG"=>"Egypt", "SV"=>"El Salvador", "GQ"=>"Equatorial Guinea", "ER"=>"Eritrea", "EE"=>"Estonia", "ET"=>"Ethiopia", "FK"=>"Falkland Islands (Malvinas)", "FO"=>"Faroe Islands", "FJ"=>"Fiji", "FI"=>"Finland", "FR"=>"France", "GF"=>"French Guiana", "PF"=>"French Polynesia", "TF"=>"French Southern Territories", "GA"=>"Gabon", "GM"=>"Gambia", "GE"=>"Georgia", "DE"=>"Germany", "GH"=>"Ghana", "GI"=>"Gibraltar", "GR"=>"Greece", "GL"=>"Greenland", "GD"=>"Grenada", "GP"=>"Guadeloupe", "GU"=>"Guam", "GT"=>"Guatemala", "GG"=>"Guernsey", "GN"=>"Guinea", "GW"=>"Guinea-Bissau", "GY"=>"Guyana", "HT"=>"Haiti", "HM"=>"Heard and Mcdonald Islands", "VA"=>"Holy See (Vatican City State)", "HN"=>"Honduras", "HU"=>"Hungary", "IS"=>"Iceland", "IN"=>"India", "ID"=>"Indonesia", "IR"=>"Iran, Islamic Republic of", "IQ"=>"Iraq", "IE"=>"Ireland", "IM"=>"Isle of Man", "IL"=>"Israel", "IT"=>"Italy", "JM"=>"Jamaica", "JP"=>"Japan", "JE"=>"Jersey", "JO"=>"Jordan", "KZ"=>"Kazakhstan", "KE"=>"Kenya", "KI"=>"Kiribati", "KP"=>"Korea (North)", "KR"=>"Korea (South)", "KW"=>"Kuwait", "KG"=>"Kyrgyzstan", "LA"=>"Lao PDR", "LV"=>"Latvia", "LB"=>"Lebanon", "LS"=>"Lesotho", "LR"=>"Liberia", "LY"=>"Libya", "LI"=>"Liechtenstein", "LT"=>"Lithuania", "LU"=>"Luxembourg", "MK"=>"Macedonia, Republic of", "MG"=>"Madagascar", "MW"=>"Malawi", "MY"=>"Malaysia", "MV"=>"Maldives", "ML"=>"Mali", "MT"=>"Malta", "MH"=>"Marshall Islands", "MQ"=>"Martinique", "MR"=>"Mauritania", "MU"=>"Mauritius", "YT"=>"Mayotte", "MX"=>"Mexico", "FM"=>"Micronesia, Federated States of", "MD"=>"Moldova", "MC"=>"Monaco", "MN"=>"Mongolia", "ME"=>"Montenegro", "MS"=>"Montserrat", "MA"=>"Morocco", "MZ"=>"Mozambique", "MM"=>"Myanmar", "NA"=>"Namibia", "NR"=>"Nauru", "NP"=>"Nepal", "NL"=>"Netherlands", "AN"=>"Netherlands Antilles", "NC"=>"New Caledonia", "NZ"=>"New Zealand", "NI"=>"Nicaragua", "NE"=>"Niger", "NG"=>"Nigeria", "NU"=>"Niue", "NF"=>"Norfolk Island", "MP"=>"Northern Mariana Islands", "NO"=>"Norway", "OM"=>"Oman", "PK"=>"Pakistan", "PW"=>"Palau", "PS"=>"Palestinian Territory", "PA"=>"Panama", "PG"=>"Papua New Guinea", "PY"=>"Paraguay", "PE"=>"Peru", "PH"=>"Philippines", "PN"=>"Pitcairn", "PL"=>"Poland", "PT"=>"Portugal", "PR"=>"Puerto Rico", "QA"=>"Qatar", "RE"=>"Réunion", "RO"=>"Romania", "RU"=>"Russian Federation", "RW"=>"Rwanda", "BL"=>"Saint-Barthélemy", "SH"=>"Saint Helena", "KN"=>"Saint Kitts and Nevis", "LC"=>"Saint Lucia", "MF"=>"Saint-Martin (French part)", "PM"=>"Saint Pierre and Miquelon", "VC"=>"Saint Vincent and Grenadines", "WS"=>"Samoa", "SM"=>"San Marino", "ST"=>"Sao Tome and Principe", "SA"=>"Saudi Arabia", "SN"=>"Senegal", "RS"=>"Serbia", "SC"=>"Seychelles", "SL"=>"Sierra Leone", "SG"=>"Singapore", "SK"=>"Slovakia", "SI"=>"Slovenia", "SB"=>"Solomon Islands", "SO"=>"Somalia", "ZA"=>"South Africa", "GS"=>"South Georgia and the South Sandwich Islands", "SS"=>"South Sudan", "ES"=>"Spain", "LK"=>"Sri Lanka", "SD"=>"Sudan", "SR"=>"Suriname", "SJ"=>"Svalbard and Jan Mayen Islands", "SZ"=>"Swaziland", "SE"=>"Sweden", "CH"=>"Switzerland", "SY"=>"Syrian Arab Republic (Syria)", "TW"=>"Taiwan, Republic of China", "TJ"=>"Tajikistan", "TZ"=>"Tanzania, United Republic of", "TH"=>"Thailand", "TL"=>"Timor-Leste", "TG"=>"Togo", "TK"=>"Tokelau", "TO"=>"Tonga", "TT"=>"Trinidad and Tobago", "TN"=>"Tunisia", "TR"=>"Turkey", "TM"=>"Turkmenistan", "TC"=>"Turks and Caicos Islands", "TV"=>"Tuvalu", "UG"=>"Uganda", "UA"=>"Ukraine", "AE"=>"United Arab Emirates", "GB"=>"United Kingdom", "US"=>"United States of America", "UM"=>"US Minor Outlying Islands", "UY"=>"Uruguay", "UZ"=>"Uzbekistan", "VU"=>"Vanuatu", "VE"=>"Venezuela (Bolivarian Republic)", "VN"=>"Viet Nam", "VI"=>"Virgin Islands, US", "WF"=>"Wallis and Futuna Islands", "EH"=>"Western Sahara", "YE"=>"Yemen", "ZM"=>"Zambia", "ZW"=>"Zimbabwe"
        );

        foreach($usersByCountry as $val) {
            $key = $val["country"];
            if (isset($countryCode[$val["country"]])) {
                $key = $countryCode[$val["country"]];
            }
            $recap["usersByCountry"][] = array("country"=>$key , "nbUsers"=>$val["nbUsers"]);
        }

        header('Content-type: application/json');
        echo json_encode($recap, JSON_PRETTY_PRINT);
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