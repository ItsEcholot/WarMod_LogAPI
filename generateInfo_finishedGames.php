<?php
/**
 * Created by Marc Berchtold (shock2provide)
 * Date: 23.06.2016
 * Time: 18:58
 */

ini_set("auto_detect_line_endings", true);

$logfile = file("testLog.log");
analyseLogFile($logfile);

function analyseLogFile($logfile)   {

    $gameIsFinished = false;
    //////////////////////////////////////////////
    $players = array();
    //
    //Players fields:
    //['name'] -> Steam Nickname
    //['userId'] -> User ID of User on Server
    //['uniqueId'] -> steamID
    //['team'] -> The number of the team the User is in
    //['kills'] -> Number of kills
    //['assists'] -> Number of assists
    //['deaths'] -> Number of deaths
    //['headshots'] -> Number of headshots
    //['teamkilss'] -> Number of teamkills
    //['damage'] -> Amount of damage dealt in the match
    /////////////////////////////////////////////
    $teams = array();
    //
    //['name'] -> Team name assigned before match started
    //['team'] -> Team number
    //['score'] -> End score of the team
    /////////////////////////////////////////////

    foreach($logfile as $line) {
        $logFileObject = json_decode($line, true);

        switch($logFileObject['event'])   {
            case "log_start":
                $logStartTimestamp = $logFileObject['unixTime'];
                break;
            case "player_status":
                $players[$logFileObject['player']['uniqueId']] = $logFileObject['player'];
                $players[$logFileObject['player']['uniqueId']]['kills'] = 0;
                $players[$logFileObject['player']['uniqueId']]['assists'] = 0;
                $players[$logFileObject['player']['uniqueId']]['deaths'] = 0;
                $players[$logFileObject['player']['uniqueId']]['headshots'] = 0;
                $players[$logFileObject['player']['uniqueId']]['teamkills'] = 0;
                $players[$logFileObject['player']['uniqueId']]['damage'] = 0;
                break;
            case "full_time":
                foreach($logFileObject['teams'] as $team) {
                    $teams[$team['team']] = $team;
                }
                $gameIsFinished = true;
                break;
            case "round_stats":
                $players[$logFileObject['player']['uniqueId']]['kills'] += $logFileObject['kills'];
                $players[$logFileObject['player']['uniqueId']]['assists'] += $logFileObject['assists'];
                $players[$logFileObject['player']['uniqueId']]['deaths'] += $logFileObject['deaths'];
                $players[$logFileObject['player']['uniqueId']]['headshots'] += $logFileObject['headshots'];
                $players[$logFileObject['player']['uniqueId']]['teamkills'] += $logFileObject['tks'];
                $players[$logFileObject['player']['uniqueId']]['damage'] += $logFileObject['damage'];
                break;
        }
    }

    print_r($teams[2]);
}