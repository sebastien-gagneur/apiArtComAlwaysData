<?php
/**
 * Step 1: Require the Slim Framework
 *
 * If you are not using Composer, you need to require the
 * Slim Framework and register its PSR-0 autoloader.
 *
 * If you are using Composer, you can skip this step.
 */
require 'Slim/Slim/Slim.php';
require 'functions.php';

\Slim\Slim::registerAutoloader();

/**
 * Step 2: Instantiate a Slim application
 *
 * This example instantiates a Slim application using
 * its default settings. However, you will usually configure
 * your Slim application now by passing an associative array
 * of setting names and values into the application constructor.
 */
    $app = new \Slim\Slim(array(
                                'debug' => true
                                ));
    
//au choix, mais ne règle pas le problème UTF-8
$app->response()->header("Content-Type", "application/json");
//$app->contentType('text/html; charset=utf-8');
    
/**
 * Step 3: Define the Slim application routes
 *
 * Here we define several Slim application routes that respond
 * to appropriate HTTP request methods. In this example, the second
 * argument for `Slim::get`, `Slim::post`, `Slim::put`, `Slim::patch`, and `Slim::delete`
 * is an anonymous function.
 */

// GET route
//GET with authentification /:db/:collection/:admin/:pass/:username/:userpass -> list all articles with tokens
$app->get(
    '/:db/:collection/:admin/:pass/:username/:userpass',
    function ($db,$collection,$admin,$pass,$username,$userpass) use ($app) {
        //echo "tous les articles !";
        //header("Content-Type: application/json");
        //header("charset=utf-8");
          echo $app->request()->getResourceUri();
          echo("\n");
        $dbhostML = 'ds045679.mongolab.com:45679';
          // Connect to test database
          // users must be read only !
          // connect with a given user
        $m1 = new Mongo("mongodb://${admin}:${pass}@${dbhostML}/${db}");
          // connect with a default user ?
          //$m1 = new Mongo("mongodb://$dbhostAD");
          //echo 'host: '.$m1;
          //echo "</br>";
          //echo 'base: '.$db1;
          //echo "</br>";
          // select the collection
        if (validAccess($db,$collection,$admin,$pass,$username,$userpass,$dbhostML))
        {
            $collection = $m1->selectDB($db)->selectCollection($collection);    // pull a cursor query
            $cursor = $collection->find();
          // juste pour tester l'encodage des caractères ...
            echo json_encode("éèçàë%ùil''l");
            echo json_encode(iterator_to_array($cursor));
        }
    }
);

//GET with authentification /:db/:collection/:admin/:pass/:username/:userpass/id/:id -> list an article with :id and tokens
$app->get(
    '/:db/:collection/:admin/:pass/:username/:userpass/id/:id',
    function ($db,$collection,$admin,$pass,$username,$userpass,$id) use ($app) {
          //echo "Un seul article : $id";
          //header("Content-Type: application/json");
          echo $app->request()->getResourceUri();
          echo("\n");
          $dbhostML = 'ds045679.mongolab.com:45679';
          // Connect to test database
          // users must be read only !
          // connect with a given user
          $m1 = new Mongo("mongodb://${admin}:${pass}@${dbhostML}/${db}");
          if (validAccess($db,$collection,$admin,$pass,$username,$userpass,$dbhostML))
          {
              $collection = $m1->selectDB($db)->selectCollection($collection);    // pull a cursor query
              $myQuery = array("id" => $id);
              $cursor = $collection->find($myQuery);
              echo json_encode(iterator_to_array($cursor));
          }
    }
);
   
//GET with authentification /:db/:collection/:admin/:pass/:username/:userpass/category/:category -> list articles by one category with tokens
$app->get(
    '/:db/:collection/:admin/:pass/:username/:userpass/category/:category',
    function ($db,$collection,$admin,$pass,$username,$userpass,$category) use ($app) {
          //echo "Un seul article : $id";
          //header("Content-Type: application/json");
          echo $app->request()->getResourceUri();
          echo("\n");
          $dbhostML = 'ds045679.mongolab.com:45679';
          // Connect to test database
          // users must be read only !
          // connect with a given user
          $m1 = new Mongo("mongodb://${admin}:${pass}@${dbhostML}/${db}");
          if (validAccess($db,$collection,$admin,$pass,$username,$userpass,$dbhostML))
          {
              $collection = $m1->selectDB($db)->selectCollection($collection);    // pull a cursor query
              $myQuery = array("category" => $category);
              $cursor = $collection->find($myQuery);
              echo json_encode(iterator_to_array($cursor));
          }
    }
);

//GET with authentification /:db/:collection/:admin/:pass/:username/:userpass/articlesorderbyrate/:order -> list all articles order by rate with tokens, order values 1 : ASC, -1 : DESC
$app->get(
    '/:db/:collection/:admin/:pass/:username/:userpass/articlesorderbyrate/:order',
    function ($db,$collection,$admin,$pass,$username,$userpass,$order) use ($app) {
          //echo "Un seul article : $id";
          //header("Content-Type: application/json");
          echo $app->request()->getResourceUri();
          echo("\n");
          $dbhostML = 'ds045679.mongolab.com:45679';
          // Connect to test database
          // users must be read only !
          // connect with a given user
          $m1 = new Mongo("mongodb://${admin}:${pass}@${dbhostML}/${db}");
          if (validAccess($db,$collection,$admin,$pass,$username,$userpass,$dbhostML))
          {
              $collection = $m1->selectDB($db)->selectCollection($collection);    // pull a cursor query
              $mySort = array("rate" => intval($order));
              $cursor = $collection->find()->sort($mySort);
              echo json_encode(iterator_to_array($cursor));
          }
    }
);
  
// POST with authentification /:db/:collection/:admin/:pass/:username/:userpass/insertarticle -> insert an article in articles collection. Parameters accept blank as value.
// /!\ RATE MUST HAVE A DEFAULT VALUE
$app->post(
    '/:db/:collection/:admin/:pass/:username/:userpass/insertarticle',
    function ($db,$collection,$admin,$pass,$username,$userpass) use ($app) {
           // le problème est que ici l'URL ressemble à ça :
           // http://techspeech.alwaysdata.net/apiartcom/artcom/articles/admin/9XTN#ztXmFnWH&/seb/seb/insertarticle?title=PROMO CHEZ KAKA&text=il y tout chez kaka&rate=2
           // donc seb doit s'arrêter au ?, sinon le pass c'est seb?name...
           //$cheminComplet = $app->request()->getPath();
           // var_dump[$_SERVER]; pour debugger les paramètres envoyer
           //echo $_GET["name"]; pour récupérer la valeur de name
           
           // LE RATE DOIT ETRE A ZERO PAR DEFAUT
           
           // L'url complète est récupérée
           $cheminComplet = $app->request()->getPath();
           // On enlève tous les paramètres après le Query
           $url = strtok($cheminComplet, '?');
           // On découpe la chaîne suivant les /
           ///:db/:collection/:admin/:pass/:username/:userpass
           $arr = explode('/', $cheminComplet);
           // la case 8 du tableau est toujours le pass !
           $userpass = $arr[7];
           //echo ':'. $userpass .':';
           
           //$id = trim(strip_tags($app->request->params('id')));
           $title = trim(strip_tags($app->request->params('title')));
           $subtitle = trim(strip_tags($app->request->params('subtitle')));
           $category = trim(strip_tags($app->request->params('category')));
           // Le blancs sont gérés sans problème
           $text = trim(strip_tags($app->request->params('text')));
           $image = trim(strip_tags($app->request->params('image')));
           $rate = trim(strip_tags($app->request->params('rate')));
           
           $dbhostML = 'ds045679.mongolab.com:45679';
           $insertOptions = array(
                                  'safe'    => true,
                                  'fsync'   => true,
                                  'timeout' => 10000
                                  );
           // Connect to test database
           // users must be not read only !
           // connect with a given user
           $m1 = new Mongo("mongodb://${admin}:${pass}@${dbhostML}/${db}");
           
           if (validAccess($db,$collection,$admin,$pass,$username,$userpass,$dbhostML))
           {
            $collection = $m1->selectDB($db)->selectCollection($collection);
            $results = $collection->insert(array(//'id' => $id,
                                                 'title' => $title,
                                                 'subtitle' => $subtitle,
                                                 'category' => $category,
                                                 'text' => $text,
                                                 'image' => $image,
                                                 'rate' => new MongoInt32($rate),
                                                 'timestamp' => new MongoTimeStamp(time())),$insertOptions);
                                                // TIME STAMP ?
            //permet de voir le id interne généré par mongodb
            //print_r($results);
           }
    }
);

// POST with authentification /:db/:collection/:admin/:pass/:username/:userpass/insertuser -> insert a user in users collection. Parameters accept blank as value.
// /!\ RATE MUST HAVE A DEFAULT VALUE
$app->post(
           '/:db/:collection/:admin/:pass/:username/:userpass/insertuser',
           function ($db,$collection,$admin,$pass,$username,$userpass) use ($app) {
           // le problème est que ici l'URL ressemble à ça :
           // http://techspeech.alwaysdata.net/apiartcom/artcom/users/admin/9XTN#ztXmFnWH&/seb/seb/insertuser?name=toto&password=toto&number=22&street=Rue des Travelles&zip=63870&city=Montrodeix&phone=0473788073&website=techspeech.fr&twitter=@sgagneur&email=toto@toto.fr&role=dealer&rate=1
           // donc seb doit s'arrêter au ?, sinon le pass c'est seb?name...
           //$cheminComplet = $app->request()->getPath();
           // var_dump[$_SERVER]; pour debugger les paramètres envoyer
           //echo $_GET["name"]; pour récupérer la valeur de name
           
           // LE RATE DOIT ETRE A ZERO PAR DEFAUT
           
           // L'url complète est récupérée
           $cheminComplet = $app->request()->getPath();
           // On enlève tous les paramètres après le Query
           $url = strtok($cheminComplet, '?');
           // On découpe la chaîne suivant les /
           ///:db/:collection/:admin/:pass/:username/:userpass
           $arr = explode('/', $cheminComplet);
           // la case 8 du tableau est toujours le pass !
           $userpass = $arr[7];
           echo ':'. $userpass .':';
           
           $name = trim(strip_tags($app->request->params('name')));
           $password = trim(strip_tags($app->request->params('password')));
           $number = trim(strip_tags($app->request->params('number')));
           $street = trim(strip_tags($app->request->params('street')));
           $zip = trim(strip_tags($app->request->params('zip')));
           $city = trim(strip_tags($app->request->params('city')));
           $phone = trim(strip_tags($app->request->params('phone')));
           $website = trim(strip_tags($app->request->params('website')));
           $twitter = trim(strip_tags($app->request->params('twitter')));
           $facebook = trim(strip_tags($app->request->params('facebook')));
           $email = trim(strip_tags($app->request->params('email')));
           $role = trim(strip_tags($app->request->params('role')));
           $rate = trim(strip_tags($app->request->params('rate')));
           
           $dbhostML = 'ds045679.mongolab.com:45679';
           
           $data = array('name' => $name,
                         'pass' => $password,
                         'number' => $number,
                         'street' => $street,
                         'zip' => new MongoInt32($zip),
                         'city' => $city,
                         'phone' => $phone,
                         'website' => $website,
                         'twitter' => $twitter,
                         'facebook' => $facebook,
                         'email' => $email,
                         'timestamp' => new MongoTimeStamp(time()),
                         'role' => $role,
                         'rate' => new MongoInt32($rate),
                         );
           
           $insertOptions = array(
                                  'safe'    => true,
                                  'fsync'   => true,
                                  'timeout' => 10000
                                  );
           // Connect to test database
           // users must be not read only !
           // connect with a given user
           $m1 = new Mongo("mongodb://${admin}:${pass}@${dbhostML}/${db}");
           echo 'avant';
           if (validAccess($db,$collection,$admin,$pass,$username,$userpass,$dbhostML))
           {
           echo 'insert';
           $collection = $m1->selectDB($db)->selectCollection($collection);
           $results = $collection->insert($data,$insertOptions);
           
           // TIME STAMP ?
           //permet de voir le id interne généré par mongodb
           print_r($results);
           }
    }
);

    
    
// PUT with authentification /:db/:collection/:admin/:pass/:username/:userpass/updatearticles/:id update an article with id set other values if define.
// /!\ USING : ALL VALUES MUST BE COMPLETED BECAUSE FIELD WITHOUT VALUE LET BLANK IN DOCUMENT.
// http://techspeech.alwaysdata.net/apiartcom/artcom/articles/admin/9XTN#ztXmFnWH&/seb/seb/updatearticles/555ca9959b8c9a2e4f8b4580?title=PROMO CHEZ KOKO&subtitle=ca a l'air de marcher&text=pas mal ce truc&rate=1
    
$app->put(
    '/:db/:collection/:admin/:pass/:username/:userpass/updatearticles/:id',
    function ($db,$collection,$admin,$pass,$username,$userpass,$id) use ($app) {
          // L'url complète est récupérée
          $cheminComplet = $app->request()->getPath();
          // On enlève tous les paramètres après le Query
          $url = strtok($cheminComplet, '?');
          // On découpe la chaîne suivant les /
          ///:db/:collection/:admin/:pass/:username/:userpass
          $arr = explode('/', $cheminComplet);
          // la case 8 du tableau est toujours le pass !
          $userpass = $arr[7];
          //echo ':'. $userpass .':';
          
          $title = trim(strip_tags($app->request->params('title')));
          $subtitle = trim(strip_tags($app->request->params('subtitle')));
          $category = trim(strip_tags($app->request->params('category')));
          // Le blancs sont gérés sans problème
          $text = trim(strip_tags($app->request->params('text')));
          $image = trim(strip_tags($app->request->params('image')));
          $rate = trim(strip_tags($app->request->params('rate')));
          
          $dbhostML = 'ds045679.mongolab.com:45679';
          
          // mise à jour si _id = $id
          $criteria = array('_id' => new MongoId($id));
          // set values title = .... and ...
          $newdata = array('$set' => array('title' => $title,
                                           'subtitle' => $subtitle,
                                           'category' => $category,
                                           'text' => $text,
                                           'image' => $image,
                                           'rate' => new MongoInt32($rate),
                                           ));
          // update options : upsert : false pas de création du document si pas trouvé, mise à jour de plusieurs articles si correspondance
          $updateOptions = array('upsert'=>false,
                                 'multiple'=>true
                                 );
          
          // Connect to test database
          // users must be not read only !
          // connect with a given user
          $m1 = new Mongo("mongodb://${admin}:${pass}@${dbhostML}/${db}");
          
          if (validAccess($db,$collection,$admin,$pass,$username,$userpass,$dbhostML))
          {
            $collection = $m1->selectDB($db)->selectCollection($collection);
            $results = $collection->update($criteria, $newdata, $updateOptions);
          
            //permet de voir le id interne généré par mongodb
            //print_r($results);
          }
    }
);

// PUT with authentification /:db/:collection/:admin/:pass/:username/:userpass/updateusers/:id update a user with id set other values if define.
// /!\ USING : ALL VALUES MUST BE COMPLETED BECAUSE FIELD WITHOUT VALUE LET BLANK IN DOCUMENT.
//http://techspeech.alwaysdata.net/apiartcom/artcom/users/admin/9XTN#ztXmFnWH&/seb/seb/updateusers/555cdf0ba9c42fc765b1290b?name=seb&password=seb&number=22&street=Rue des Travelles&zip=63870&city=Montrodeix&phone=0473788073&website=techspeech.fr&twitter=@sgagneur&email=toto@toto.fr&role=admin&rate=99
$app->put(
          '/:db/:collection/:admin/:pass/:username/:userpass/updateusers/:id',
          function ($db,$collection,$admin,$pass,$username,$userpass,$id) use ($app) {
          // L'url complète est récupérée
          $cheminComplet = $app->request()->getPath();
          // On enlève tous les paramètres après le Query
          $url = strtok($cheminComplet, '?');
          // On découpe la chaîne suivant les /
          ///:db/:collection/:admin/:pass/:username/:userpass
          $arr = explode('/', $cheminComplet);
          // la case 8 du tableau est toujours le pass !
          $userpass = $arr[7];
          //echo ':'. $userpass .':';
          
          $name = trim(strip_tags($app->request->params('name')));
          $password = trim(strip_tags($app->request->params('password')));
          $number = trim(strip_tags($app->request->params('number')));
          $street = trim(strip_tags($app->request->params('street')));
          $zip = trim(strip_tags($app->request->params('zip')));
          $phone = trim(strip_tags($app->request->params('phone')));
          $website = trim(strip_tags($app->request->params('website')));
          $twitter = trim(strip_tags($app->request->params('twitter')));
          $facebook = trim(strip_tags($app->request->params('facebook')));
          $email = trim(strip_tags($app->request->params('email')));
          $role = trim(strip_tags($app->request->params('role')));
          $rate = trim(strip_tags($app->request->params('rate')));
          
          $dbhostML = 'ds045679.mongolab.com:45679';
          
          // mise à jour si _id = $id
          $criteria = array('_id' => new MongoId($id));
          // set values title = .... and ...
          $newdata = array('$set' => array('name' => $name,
                                           'pass' => $password,
                                           'number' => $number,
                                           'street' => $street,
                                           'zip' => $zip,
                                           'city' => $city,
                                           'phone' => $phone,
                                           'website' => $website,
                                           'twitter' => $twitter,
                                           'facebook' => $facebook,
                                           'email' => $email,
                                           'timestamp' => new MongoTimeStamp(time()),
                                           'role' => $role,
                                           'rate' => new MongoInt32($rate),
                                           ));
          // update options : upsert : false pas de création du document si pas trouvé, mise à jour de plusieurs articles si correspondance
          $updateOptions = array('upsert'=>false,
                                 'multiple'=>true
                                 );
          
          // Connect to test database
          // users must be not read only !
          // connect with a given user
          $m1 = new Mongo("mongodb://${admin}:${pass}@${dbhostML}/${db}");
          
          if (validAccess($db,$collection,$admin,$pass,$username,$userpass,$dbhostML))
          {
          $collection = $m1->selectDB($db)->selectCollection($collection);
          $results = $collection->update($criteria, $newdata, $updateOptions);
          
          //permet de voir le id interne généré par mongodb
          //print_r($results);
          }
    }
);

    
// PATCH route
$app->patch('/patch', function () {
    echo 'This is a PATCH route';
});
    

// DELETE with authentification : /:db/:collection/:admin/:pass/:username/:userpass/deleteuser/:id : delete a user with id.
//http://techspeech.alwaysdata.net/apiartcom/artcom/users/admin/9XTN#ztXmFnWH&/seb/seb/deleteuser/555cdf529b8c9a2e4f8b4586
$app->delete(
    '/:db/:collection/:admin/:pass/:username/:userpass/deleteuser/:id',
    function ($db,$collection,$admin,$pass,$username,$userpass,$id) use ($app) {
             $dbhostML = 'ds045679.mongolab.com:45679';
             
             // mise à jour si _id = $id
             $criteria = array('_id' => new MongoId($id));
             // set values title = .... and ...
             
             // remove options : upsert : false pas de création du document si pas trouvé, mise à jour de plusieurs articles si correspondance
             $removeOptions = array('justOne'=>true,
                                    'j'=>false
                                    );
             
             // Connect to test database
             // users must be not read only !
             // connect with a given user
             $m1 = new Mongo("mongodb://${admin}:${pass}@${dbhostML}/${db}");
             
             if (validAccess($db,$collection,$admin,$pass,$username,$userpass,$dbhostML))
             {
             $collection = $m1->selectDB($db)->selectCollection($collection);
             $results = $collection->remove($criteria, $removeOptions);
             
             //permet de voir le id interne généré par mongodb
             //print_r($results);
             }

    }
);

// DELETE with authentification : /:db/:collection/:admin/:pass/:username/:userpass/deletearticle/:id : delete an article with id.
//http://techspeech.alwaysdata.net/apiartcom/artcom/articles/admin/9XTN#ztXmFnWH&/seb/seb/deletearticle/555b6b2f9b8c9a2e4f8b457e
$app->delete(
             '/:db/:collection/:admin/:pass/:username/:userpass/deletearticle/:id',
             function ($db,$collection,$admin,$pass,$username,$userpass,$id) use ($app) {
             $dbhostML = 'ds045679.mongolab.com:45679';
             
             // mise à jour si _id = $id
             $criteria = array('_id' => new MongoId($id));
             echo $id;
             // set values title = .... and ...
             
             // remove options : upsert : false pas de création du document si pas trouvé, mise à jour de plusieurs articles si correspondance
             $removeOptions = array('justOne'=>true,
                                    'j'=>false
                                    );
             
             // Connect to test database
             // users must be not read only !
             // connect with a given user
             $m1 = new Mongo("mongodb://${admin}:${pass}@${dbhostML}/${db}");
             
             if (validAccess($db,$collection,$admin,$pass,$username,$userpass,$dbhostML))
             {
             echo 'ok';
             $collection = $m1->selectDB($db)->selectCollection($collection);
             $results = $collection->remove($criteria, $removeOptions);
             
             //permet de voir le id interne généré par mongodb
                print_r($results);
             }
    }
);
    
/**
 * Step 4: Run the Slim application
 *
 * This method should be called last. This executes the Slim application
 * and returns the HTTP response to the HTTP client.
 */
$app->run();
?>
