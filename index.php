<?php

use Tet\HTTP\Response;
use Tet\Fasades;
use Tet\MySQL;
use Tet\Query;
use Tet\Tet;

include("src/Tet.php");

class Calc
{
    function A(Fasades $fasades)
    {
        $r = new Response();
        $r->body = $fasades->getParams()->get("a");
        $r->code = 200;
        return $r;
    }

    function B()
    {
        return "bbb";
    }
}


$app = new Tet;
$db = $app->getFasades()->getDb(new MySQL);
$db->open('localhost', 'snab_market_new','root', '');


//include("./TableNames.php");
//include("./db_scheme/fias.php");

//print_r($f::SCHEME);
//TableNames::OC_API_SESSION;

/**
 * нужно создать такой класс с описанием схемы таблицы, чтобы:
 * - его можно было использовать при формировании запросов, чтобы названия полей предлагались
 * - на его основе можно было бы обновлять структуры таблицы в самой базе:
 * - - зашел в файл с классом,
 * - - добавил нужное свойство класса,
 * - - вызвал метод обновления структуры базы
 * - - структура базы обновилась
 * 
*/

$tables = $db->getTableList();
foreach($tables as $table)
{
    $db->createTableSchemeClass("App\Db", $table);
}
//$rr = $db->getTableScheme('fias');

//echo json_encode($rr->toArray());


//$app->getFasades()->getUtils()->createtEnumerator("db_scheme", "TableNames", $tables);


// $qry = new Query;
// $qry->tablename = "oc_address";
// $qry->command = $qry::COMMAND_SELECT;
// $qry->fields->add(['*']);
//$qry->where = "id = 5";
//$qry->orderBy = ["id", "count DESC"];
//echo $qry . "<br>";
//print_r($db->execute($qry)->data);




$db->close();



//$app = new Tet;
//$app->getFasades()->getDb(new MySQL)->db;


// $params = $app->getFasades()->getParams();
// $params->set("a", 6);
// $router = $app->getRouter();
// $router->get("/{id}/{g}", function ($app, $args){
//    print_r($args);
//     return "root";
// });

// $router->get("/files", function () {
//     return "files!";
// });

// $router->get("/a", [(new Calc), 'A']);

// $router->get("/b", [(new Calc), 'B']);

// $router->get("/c", function () use ($app) {
//     return "!";
// });

// $app->run();
