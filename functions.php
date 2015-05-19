<?php

    function validAccess($name,$pass) {
        $result = FALSE;
        $dbhostAD = 'mongodb-techspeech.alwaysdata.net';
        $dbhostML = 'ds045679.mongolab.com:45679';
        $dbnameAD = 'techspeech_artcom';
        $dbnameML = 'artcom';
        
        // Connect to test database
        $password = "QMBD35BEI";
        // users must be read only !
        $usernameAD = "techspeech_db";
        $usernameML = "root";
        // connect with a given user
        $m1 = new Mongo("mongodb://${usernameML}:${password}@${dbhostML}/artcom");
        // connect with a default user ?
        //$m1 = new Mongo("mongodb://$dbhostAD");
        //echo 'host: '.$m1;
        //echo "</br>";
        $db1 = $m1->$dbnameAD;
        //echo 'base: '.$db1;
        $collection = $m1->selectDB("artcom")->selectCollection("users");    // pull a cursor query
        //$cursor = $collection->find();
        //$myQuery = array("name" => $name);
        $myQuery = array('$and' => array(array("name" => $name), array("pass" => $pass)));
        $cursor = $collection->find($myQuery);
        if ($cursor->count() > 0)
        {
            $result = TRUE;
        }
        return $result;
    }
?>