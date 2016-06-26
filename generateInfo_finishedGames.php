<?php
/**
 * Created by Marc Berchtold (shock2provide)
 * Date: 23.06.2016
 * Time: 18:58
 */

ini_set("auto_detect_line_endings", true);

analyseAllLogFilesInDirectory();
function analyseAllLogFilesInDirectory()    {
    $files = glob("*.log", GLOB_BRACE);

//Define your database settings here
    $mysqlConnection = new mysqli($servername, $username, $password, $dbname);

    foreach($files as $fileName) {
        $logfile = file($fileName);

        $mysqlRow = $mysqlConnection->query("SELECT gameIsFinished FROM warMod_matches WHERE fileName='".substr($fileName, 0, -4)."' LIMIT 1");

        if ($mysqlRow->num_rows == 0) {
            $result = analyseLogFile($logfile);

            $mysqlConnection->query("INSERT INTO warMod_matches (fileName, gameIsFinished) VALUES ('".substr($fileName, 0, -4)."', ".$result['gameIsFinished'].")");
            $warMod_matchId = $mysqlConnection->insert_id;

            $warMod_teamIds = array();
            foreach($result['teams'] as $team)  {
                $mysqlConnection->query("INSERT INTO warMod_teams (matchId, name, team, score) VALUES (".$warMod_matchId.", '".$team['name']."', ".$team['team'].", ".$team['score'].")");
                $warMod_teamIds[$team['team']] = $mysqlConnection->insert_id;
            }

            foreach($result['players'] as $player)  {
                $mysqlConnection->query("INSERT INTO warMod_players (matchId, teamId, name, userId, uniqueId, team, kills, assists, deaths, headshots, teamkills, damage) VALUES (".$warMod_matchId.", ".$warMod_teamIds[$player['team']].", '".$player['name']."', ".$player['userId'].", '".$player['uniqueId']."', ".$player['team'].", ".$player['kills'].", ".$player['assists'].", ".$player['deaths'].", ".$player['headshots'].", ".$player['teamkills'].", ".$player['damage'].")");
            }
        }
    }
}


/**
 * @param $logfile
 * @return array
 *  --> ['gameIsFinished'] -> Is the match already finished?
 *  --> ['players'] -> Contains the players array
 *  --> ['teams'] -> Contains the teams array
 */
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
    //['teamkills'] -> Number of teamkills
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

    return array(   "gameIsFinished" => $gameIsFinished,
                    "players" => $players,
                    "teams" => $teams
                );
}