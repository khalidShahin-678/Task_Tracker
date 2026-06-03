<?php
// Task Tracker

// Global variables
define("FILE_NAME","Tasks.json");
define("STATUS_TODO", "to do");
define("STATUS_IN_PROGRESS", "in progress");
define("STATUS_DONE", "done");
$Tasks = [];
$indexing = 0;

// Define the structre of Task 

/*
    int      ID
    string   description
    string   status
    string   createdAt
    string   updatedAt

    To organize Task adding and updating and deleting 
    we will define some of functions to handling these operations
*/

// Define Functions 

// CRUD 
// For reading you can accessing $Tasks array directly 
function addTask(array &$Tasks,int &$indexing,string $description):void{
    // valid input?
    if($description == '')return;
    // create Task
    $newTask=[];
    $newTask["ID"] = $indexing + 1 ;
    $newTask["description"] = $description;
    $newTask["status"] = STATUS_TODO;
    $newTask["createdAt"] = date("Y-m-d H:i:s");
    $newTask["updatedAt"] = null;
    // end creating
    // increment indexing and Add to Tasks
    $indexing++;
    array_push($Tasks,$newTask);
    echo "✓ Task added successfully (ID: {$newTask['ID']})\n";
}
function updateTask(array &$Tasks,int $ID,string $newDescription):bool{
    // valid input?
    if($ID<=0)return false;
    if($newDescription == "") return false;
    // task exist ?
    for ($i = 0;$i<count($Tasks);$i++){
        if($Tasks[$i]["ID"] == $ID){
            $Tasks[$i]["description"]= $newDescription;
            $Tasks[$i]["updatedAt"]  = date("Y-m-d H:i:s");
            echo "✓ Task {$ID} updated successfully\n";
            return true;
        }
    }
    echo "✗ Task {$ID} not found\n";
    return false;
}
function deleteTask(array &$Tasks,int $ID):bool{
    // valid input?
    if($ID<=0)return false;
    // task exist ?
    for ($i = 0;$i<count($Tasks);$i++){
        if($Tasks[$i]["ID"] == $ID){
            array_splice($Tasks,$i,1);
            echo "✓ Task {$ID} deleted successfully\n";
            return true;
        }
    }
    echo "✗ Task {$ID} not found\n";
    return false;
}
function setStatus(array &$Tasks,int $ID,string $status):bool{
    for ($i = 0;$i<count($Tasks);$i++){
        // task exist ?
        if($Tasks[$i]["ID"] == $ID){
            $Tasks[$i]["status"]= $status;
            $Tasks[$i]["updatedAt"]  = date("Y-m-d H:i:s");
            echo "✓ Task {$ID} marked as '{$status}'\n";
            return true;
        }
    }
    echo "✗ Task {$ID} not found\n";
    return false;
}
// end
// Storage in Files
function saveTasks (array $Tasks ,int $indexing):void{
    $data = [];
    $data["indexing"] = $indexing;
    $data["tasks"] = $Tasks;
    $jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    file_put_contents(FILE_NAME,$jsonContent);
}
function loadTasks(array &$Tasks,&$indexing):void{
    if(!file_exists(FILE_NAME)){
        $Tasks =[];
        $indexing = 0;
        return;
    }
    $jsondata = file_get_contents(FILE_NAME);
    $data = json_decode($jsondata,true);
    $indexing = $data["indexing"] ?? 0;
    $Tasks = $data["tasks"] ?? [];
}
//end

// List Tasks
// end
function listTasks($Tasks){
    if(empty($Tasks)){
        echo "no tasks found!";
        return;
    }
    echo str_pad("Tasks",120,"=",STR_PAD_BOTH);
    echo "\nID  |     status     |                               description                               |     created at     |     updated at    \n";
    foreach($Tasks as $Task){
        $id = str_pad($Task['ID'],4," ",STR_PAD_BOTH);
        $status = str_pad($Task['status'],16," ",STR_PAD_BOTH);
        $description = str_pad($Task['description'],72," ",STR_PAD_RIGHT);
        $createdAt = str_pad($Task['createdAt'],20," ",STR_PAD_BOTH);
        $updatedAt = str_pad($Task['updatedAt'] ?? "—",20," ",STR_PAD_BOTH);
        printf("%s|%s|%s|%s|%s\n",$id,$status,$description,$createdAt,$updatedAt);
    }
}
function listTasksByStatus($Tasks , $status){
    if(empty($Tasks)){
        echo "no tasks found!";
        return;
    }
    echo str_pad("Tasks are $status",120,"=",STR_PAD_BOTH);
    echo "\nID  |                               description                               |     created at     |     updated at    \n";
    foreach($Tasks as $Task){
        if($Task['status'] !== $status){continue;}
        $id = str_pad($Task['ID'],4," ",STR_PAD_RIGHT);
        $description = str_pad($Task['description'],72," ",STR_PAD_RIGHT);
        $createdAt = str_pad($Task['createdAt'],20," ",STR_PAD_BOTH);
        $updatedAt = str_pad($Task['updatedAt']??"_",20," ",STR_PAD_BOTH);
        printf("%s|%s|%s|%s\n",$id,$description,$createdAt,$updatedAt);
        // echo "\n";
    }
}
// Handle User Choices
if(!isset($argv[1])){
    echo "Usage: php index.php [command] [args]\n";
    echo "Commands: add, delete, update, mark-done, mark-in-progress, mark-to-do, list\n";
    exit();
}
loadTasks($Tasks,$indexing);   // load Tasks
switch ($argv[1]) {
    case 'add':
        if(!isset($argv[2])){
            echo "Error: description required\n";
            exit();
        }
        addTask($Tasks,$indexing,$argv[2]);
        break;
    case 'delete':
        if(!isset($argv[2])){
            echo "Error: task ID required\n";
            exit();
        }
        deleteTask($Tasks,(int)$argv[2]);
        break;
    case 'update':
        if(!isset($argv[2])||!isset($argv[3])){
            echo "Error: task ID and description required\n";
            exit();
        }
        updateTask($Tasks,(int)$argv[2],$argv[3]);
        break;
    case 'mark-done':
        if(!isset($argv[2])){
            echo "Error: task ID required\n";
            exit();
        }
        setStatus($Tasks,(int)$argv[2],STATUS_DONE);
        break;
    case 'mark-in-progress':
        if(!isset($argv[2])){
            echo "Error: task ID required\n";
            exit();
        }
        setStatus($Tasks,(int)$argv[2],STATUS_IN_PROGRESS);
        break;
    case 'mark-to-do':
        if(!isset($argv[2])){
            echo "Error: task ID required\n";
            exit();
        }
        setStatus($Tasks,(int)$argv[2],STATUS_TODO);
        break;
    case 'list' :
        listTasks($Tasks);
        break;
    case 'list-done':
        listTasksByStatus($Tasks,STATUS_DONE);
        break;
    case 'list-in-progress':
        listTasksByStatus($Tasks,STATUS_IN_PROGRESS);
        break;
    case 'list-to-do':
        listTasksByStatus($Tasks,STATUS_TODO);
        break;
    default:
        echo "Error: unknown command '{$argv[1]}'\n";
    break;
}
saveTasks($Tasks,$indexing);
?>