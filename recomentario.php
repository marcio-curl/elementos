<?php
ini_set('default_charset','UTF-8');

// Configuração do MySQL
require("mysql.inc.php");
$link = mysql_connect($mysql_host, $mysql_user, $mysql_password) or die('Erro ao conectar o MySQL: ' . mysql_error());
mysql_select_db($mysql_database) or die('Erro ao conectar o banco de dados');	
mysql_set_charset('utf8');
mysql_query('SET character_set_results=utf8');

$db_recomentarios = 'recomentarios';

session_start();

if (is_numeric($_POST['aval']) && is_numeric($_POST['referencia']) && is_string($_POST['texto']))
{
	$id = session_id();
		
	$query = sprintf('INSERT INTO %s (session_id, referencia, avaliacao, texto) VALUES ("%s", %d, %d, "%s")', $db_recomentarios, 
					$id, mysql_real_escape_string($_POST['referencia']), mysql_real_escape_string($_POST['aval']), mysql_real_escape_string($_POST['texto']));		
	if (!mysql_query($query))
		die('Erro ao gravar as respostas: ' . mysql_error());

	echo "true";
}

if ($link)
	mysql_close($link);
?>