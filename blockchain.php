<?
header('Access-Control-Allow-Origin: *');
ob_start('ob_gzhandler');
date_default_timezone_set("Jamaica");
define("timezone", "EST");

//THE STARTING BLOCK OF THE BLOCKCHAIN
function genesisBlock(){
    $genesisBlock = new \stdClass();
    $genesisBlock->index = 0;
    $genesisBlock->date = date("Y-m-d");
    $genesisBlock->time = date("H:i:s")." ".timezone;
    $genesisBlock->data = "[]";
    $genesisBlock->previousHash = null;
    $genesisBlock->hash = md5(json_encode($genesisBlock));
    return $genesisBlock;
}

//SAVE ANY APPENICES TO THE BLOCKCHAIN TO FILE
function saveChanges(){
    global $blockchain;
    $myfile = fopen("blockchain.txt", "w"); 
    fwrite($myfile, gzcompress(json_encode($blockchain)));
    fclose($myfile);
}


if(file_exists("blockchain.txt")){
    $myfile = fopen("blockchain.txt", "r");
    $blockchain = json_decode(gzuncompress(file_get_contents("blockchain.txt")));
    fclose($myfile);
}
else{
    $blockchain = array();
    array_push($blockchain, genesisBlock()); 
    saveChanges();
}


//GOES THROUGH ALL HASHES AND CHECKS IF THEY FLOW
//WHERE THE FLOW IS THE MD5 HASH OF THE PREVIOUS BLOCK
//WHERE A BLOCK IS A SINGLE COMPONENT OF THE BLOCKCHAIN
function validateChain(){
    global $blockchain;
    if(count($blockchain) > 1){
        for($x = 0; $x < count($blockchain); $x++){
            $tmpBlock = new \stdClass();
            $tmpBlock->index = $blockchain[$x]->index;
            $tmpBlock->date = $blockchain[$x]->date;
            $tmpBlock->time = $blockchain[$x]->time;
            $tmpBlock->data = $blockchain[$x]->data; 
            $tmpBlock->previousHash = $blockchain[$x]->previousHash; 
            $tmpBlock->hash = md5(json_encode($tmpBlock));
            if($tmpBlock->hash != $blockchain[$x]->hash){
                return false;
            } 
        }
        for($x = 1; $x < count($blockchain); $x++){
            if($blockchain[$x]->previousHash != $blockchain[$x - 1]->hash){
                return false;
            }
        }
    }
    return true;
}

if(validateChain() == false){
    die("THE BLOCKCHAIN HAS LOST ITS INTEGRITY AND WILL NOT BE USED");
}

//GENERATES A NEW BLOCK TO BE APPENDED TO THE BLOCKCHAIN
//THE NEW HASH IS THE MD5 SUM OF THE PREVIOUS BLOCK HASH
function generateBlock($data){
    global $blockchain;
    $block = new \stdClass();
    $block->index = count($blockchain);
    $block->date = date("Y-m-d")." EST";
    $block->time = date("H:i:s");
    $block->data = $data;
    $block->previousHash = $blockchain[count($blockchain) - 1]->hash;
    $block->hash = md5(json_encode($block));
    return $block;
}

//APPENDS THE BLOCK TO THE BLOCKCHAIN
function addBlock($data){
    global $blockchain;
    $newBlock = generateBlock($data);
    array_push($blockchain, $newBlock);
    saveChanges();
}


echo json_encode($blockchain);


if(isset($_POST['transaction']) && isset($_POST['key'])){
    //VERIFICATION THAT TRANSACTION HAS COME FROM A USERS WALLET
    
    //USING THE TRANSATION OBJECT eg: {"SENDER":"id", "RECEIVER":"id", "AMOUNT":10000, "PW_HASH":"86fb269d190d2c85f6e0468ceca42a20"}
    //PW_HASH IS THE HASH OF THE PASSWORD ENTERED AND WILL BE USED TO CHECK IF THE ENTERED PASSWORD MATCHES THE ACTUAL 
    //USER PASSWORD IN THE DATABASE. IF THIS IS THE CASE THEN THE PURCHASE HAS BEEN MADE FROM THE USERS WALLET WITH
    //THEIR CONSCENT
    
    $tran = $_POST['transaction'];
    
    function verification($pw){
        //CONNECT TO DATABASE AND PASSWORD VERIFICATION HERE
        return false;
    }
    
    if(verification($tran->PW_HASH)){
        addBlock($_POST['transaction']);
        echo true; 
    }
    else{
        echo false;
    }
    header('Location: '.$_SERVER['REQUEST_URI']); 
}
