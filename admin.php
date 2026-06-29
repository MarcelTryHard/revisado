<?php

session_start();

require_once 'config.php';



if(
empty($_SESSION['usuario_id']) ||
($_SESSION['usuario_tipo'] ?? '') !== 'admin'
){

header("Location: login.php");

exit();

}



if(empty($_SESSION['csrf_token'])){

$_SESSION['csrf_token'] =
bin2hex(random_bytes(32));

}




/*
========================
AÇÕES ADMIN
========================
*/


if($_SERVER['REQUEST_METHOD']=="POST"){


if(
!hash_equals(
$_SESSION['csrf_token'],
$_POST['csrf_token']
)

){

die("Token inválido");

}



$acao=$_POST['acao'];



/*
CADASTRAR SERVIÇO
*/


if($acao=="novo_servico"){


$stmt=$conn->prepare(

"INSERT INTO servicos
(nome,descricao,preco,duracao)
VALUES(?,?,?,?)"

);



$stmt->bind_param(

"ssdi",

$_POST['nome'],

$_POST['descricao'],

$_POST['preco'],

$_POST['duracao']

);



$stmt->execute();


}




/*
DELETAR SERVIÇO
*/


if($acao=="deletar_servico"){


$id=$_POST['id'];


$stmt=$conn->prepare(

"DELETE FROM servicos WHERE id=?"

);


$stmt->bind_param("i",$id);


$stmt->execute();



}



/*
ALTERAR STATUS AGENDAMENTO
*/


if($acao=="status"){



$stmt=$conn->prepare(

"UPDATE agendamentos

SET status=?

WHERE id=?"

);



$stmt->bind_param(

"si",

$_POST['status'],

$_POST['id']

);



$stmt->execute();



}



}





/*
========================
DADOS
========================
*/


$totalAgendamentos =
$conn->query(

"SELECT COUNT(*) total
FROM agendamentos"

)
->fetch_assoc()['total'];



$totalClientes =
$conn->query(

"SELECT COUNT(*) total
FROM clientes"

)
->fetch_assoc()['total'] ?? 0;




$totalServicos =
$conn->query(

"SELECT COUNT(*) total
FROM servicos"

)
->fetch_assoc()['total'];




$faturamento =
$conn->query(

"SELECT SUM(valor) total
FROM pagamentos"

)
->fetch_assoc()['total'] ?? 0;



$servicos =

$conn->query(

"SELECT *
FROM servicos
ORDER BY id DESC"

)
->fetch_all(MYSQLI_ASSOC);




$agendamentos =

$conn->query(

"SELECT *
FROM agendamentos
ORDER BY data DESC"

)
->fetch_all(MYSQLI_ASSOC);



?>