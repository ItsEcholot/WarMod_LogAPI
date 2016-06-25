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
    $players = array();
    $teams = array();

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
                    array_push($teams, $team);
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

    print_r($players);
}