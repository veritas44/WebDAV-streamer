<?php

/**
 * Created by PhpStorm.
 * User: Koen
 * Date: 3-5-2016
 * Time: 20:59
 */
class Playlist
{

    var $file;
    var $folder;

    function remove_linebreaks($string){
        return preg_replace( "/\r|\n/", "", $string);
    }

    function __construct($filename, $folder)
    {
        $this->file = $filename;
        $this->folder = dirname(urldecode($folder));
    }

    function openPLS(){
        $items = parse_ini_file($this->file, true, INI_SCANNER_RAW);
        //var_dump($items);
        $fileArray = array();
        $items = array_change_key_case($items);
        $items["playlist"] = array_change_key_case($items["playlist"]);
        for($i = 1; $i <= $items["playlist"]["numberofentries"]; $i++){
            $fullPath = $this->remove_linebreaks(str_replace("http://dummy", "", url_to_absolute("http://dummy" . $this->folder . "/", str_replace("\\", "/", $items["playlist"]["file" . $i]))));
            $fullPath = str_replace(' ', '%20', $fullPath);
            $fullPath = urlencode($fullPath);
            if(array_key_exists("title" . $i, $items["playlist"])){
                $nameOnly = $this->remove_linebreaks($items["playlist"]["title" . $i]);
            } else {
                $nameOnly = $this->remove_linebreaks($items["playlist"]["file" . $i]);
                //$nameOnly = str_replace(' ', '%20', $nameOnly);
                $nameOnly = basename($nameOnly);
            }
            $nameOnly = urlencode($nameOnly);
            $fileArray[] = array($fullPath, $nameOnly);
        }
        return $fileArray;
    }
    
    function savePLS($jsonPlaylist){
        global $client;
        $decodedPlaylist = json_decode($jsonPlaylist, true);
        $plsPlaylist = "[playlist]\n";
        $i = 0;
        foreach ($decodedPlaylist as $item){
            $i++;
            $file = $item["mp3"];
            $file = strstr($file, "get_file.php?file=");
            $file = str_replace("get_file.php?file=", "", $file);
            $file = $this->getRelativePath(urldecode($this->file), urldecode($file));

            $plsPlaylist .= "File" . $i . "=" . $file . "\n";
        }
        $plsPlaylist .= "NumberOfEntries=" . $i;

        //echo $plsPlaylist;
        $this->file = str_replace(' ', '%20', $this->file);
        //echo $this->file;
        return $client->request('PUT', $this->file, $plsPlaylist);
    }

    function openM3U(){
        $handle = fopen($this->file, "r");
        $fileArray = array();
        $currentSong = "";
        while (($line = fgets($handle)) !== false) {
            if($this->remove_linebreaks($line) == ""){
                continue;
            }
            if(starts_with($line, "#EXT")){
                if(starts_with($line, "#EXTINF")){
                    $currentSong = explode(",", $line)[1];
                } else {
                    $currentSong = "";
                }
                continue;
            }
            $line = $this->remove_linebreaks($line);
            $line = str_replace("\\", "/", $line);
            $fullPath = (str_replace("http://dummy", "", url_to_absolute("http://dummy" . $this->folder . "/", $line)));
            $fullPath = str_replace(' ', '%20', $fullPath);
            $fullPath = urlencode($fullPath);
            if(empty($currentSong)){
                $nameOnly = $this->remove_linebreaks($line);
                //$nameOnly = str_replace(' ', '%20', $nameOnly);
                $nameOnly = basename($nameOnly);
            } else {
                $nameOnly = $currentSong;
            }
            $nameOnly = urlencode($nameOnly);
            $fileArray[] = array($fullPath, $nameOnly);
        }
        fclose($handle);
        return $fileArray;
    }

    function saveM3U($jsonPlaylist){
        global $client;
        $decodedPlaylist = json_decode($jsonPlaylist, true);
        $m3uPlaylist = "";
        foreach ($decodedPlaylist as $item){
            $file = $item["mp3"];
            $file = strstr($file, "get_file.php?file=");
            $file = str_replace("get_file.php?file=", "", $file);
            $file = $this->getRelativePath(urldecode($this->file), urldecode($file));

            $m3uPlaylist .= $file . "\n";
        }

        //echo $plsPlaylist;
        $this->file = str_replace(' ', '%20', $this->file);
        //echo $this->file;
        return $client->request('PUT', $this->file, $m3uPlaylist);
    }

    function getRelativePath($from, $to)
    {
        // some compatibility fixes for Windows paths
        $from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
        $to   = is_dir($to)   ? rtrim($to, '\/') . '/'   : $to;
        $from = str_replace('\\', '/', $from);
        $to   = str_replace('\\', '/', $to);

        $from     = explode('/', $from);
        $to       = explode('/', $to);
        $relPath  = $to;

        foreach($from as $depth => $dir) {
            // find first non-matching dir
            if($dir === $to[$depth]) {
                // ignore this directory
                array_shift($relPath);
            } else {
                // get number of remaining dirs to $from
                $remaining = count($from) - $depth;
                if($remaining > 1) {
                    // add traversals up to first matching dir
                    $padLength = (count($relPath) + $remaining - 1) * -1;
                    $relPath = array_pad($relPath, $padLength, '..');
                    break;
                } else {
                    $relPath[0] = './' . $relPath[0];
                }
            }
        }
        return implode('/', $relPath);
    }
}