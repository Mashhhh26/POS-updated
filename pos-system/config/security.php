<?php
if(session_status()!==PHP_SESSION_ACTIVE)session_start();
const MAX_PRICE=9999999.99; const MAX_QTY=1000000; const MAX_PERCENT=100;
function csrf_token():string{if(empty($_SESSION['csrf_token']))$_SESSION['csrf_token']=bin2hex(random_bytes(32));return $_SESSION['csrf_token'];}
function csrf_field():string{return '<input type="hidden" name="csrf_token" value="'.htmlspecialchars(csrf_token(),ENT_QUOTES,'UTF-8').'">';}
function verify_csrf():void{if($_SERVER['REQUEST_METHOD']!=='POST')return;$t=$_POST['csrf_token']??'';if(!hash_equals($_SESSION['csrf_token']??'',$t)){http_response_code(403);exit('Invalid or expired security token.');}}
function valid_money($v,bool $zero=true):bool{if(!is_numeric($v))return false;$n=(float)$v;return is_finite($n)&&($zero?$n>=0:$n>0)&&$n<=MAX_PRICE&&round($n,2)==$n;}
function valid_qty($v,bool $zero=false):bool{if(filter_var($v,FILTER_VALIDATE_INT)===false)return false;$n=(int)$v;return ($zero?$n>=0:$n>0)&&$n<=MAX_QTY;}
function valid_percent($v):bool{return is_numeric($v)&&(float)$v>=0&&(float)$v<=MAX_PERCENT;}
function require_permission(string $p):void{if(!isset($_SESSION['user_id'])){header('Location: '.BASE_PATH.'login.php');exit();}if((int)($_SESSION['role_id']??0)===1||hasPermission($p))return;logActivity('Access denied: '.$p);header('Location: '.BASE_PATH.'index.php?error=access_denied');exit();}
function h($v):string{return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
