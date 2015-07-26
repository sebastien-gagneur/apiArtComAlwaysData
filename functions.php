<?php

    function validAccess($db,$collection,$admin,$pass,$username,$userpass,$dbhostML) {
        $result = FALSE;

        // Connect to test database
        // users must be read only !
        // connect with a given user
        $m1 = new Mongo("mongodb://${admin}:${pass}@${dbhostML}/${db}");
        // connect with a default user ?
        //$m1 = new Mongo("mongodb://$dbhostAD");
        //echo 'host: '.$m1;
        //echo "</br>";
        //$db1 = $m1->$dbnameAD;
        //echo 'base: '.$db1;
        $collection = $m1->selectDB($db)->selectCollection("users");    // pull a cursor query
        //$cursor = $collection->find();
        //$myQuery = array("name" => $name);
        $myQuery = array('$and' => array(array("name" => $username), array("pass" => $userpass)));
        $cursor = $collection->find($myQuery);
        if ($cursor->count() > 0)
        {
            $result = TRUE;
        }
        return $result;
    }
    
    // this function checks access only with name without pass
    function validAccessSignIn($db,$collection,$admin,$pass,$username,$userpass,$dbhostML) {
        $result = FALSE;
        
        // Connect to test database
        // users must be read only !
        // connect with a given user
        $m1 = new Mongo("mongodb://${admin}:${pass}@${dbhostML}/${db}");
        // connect with a default user ?
        //$m1 = new Mongo("mongodb://$dbhostAD");
        //echo 'host: '.$m1;
        //echo "</br>";
        //$db1 = $m1->$dbnameAD;
        //echo 'base: '.$db;
        $collection = $m1->selectDB($db)->selectCollection("users");    // pull a cursor query
        //$cursor = $collection->find();
        //$myQuery = array("name" => $name);
        $myQuery = array("name" => $username);
        $cursor = $collection->find($myQuery);
        if ($cursor->count() > 0)
        {
            $result = TRUE;
        }
        return $result;
    }
?>