<?php
ini_set('default_charset','UTF-8');

// Configuração do MySQL
require("mysql.inc.php");
$link = mysql_connect($mysql_host, $mysql_user, $mysql_password) or die('Erro ao conectar o MySQL: ' . mysql_error());
mysql_select_db($mysql_database) or die('Erro ao conectar o banco de dados');	
mysql_set_charset('utf8');
mysql_query('SET character_set_results=utf8');

$db_questoes = 'questoes';
$db_respostas = 'respostas';
$db_comentarios = 'comentarios';

session_start();
//session_destroy();

// Sorteio das questões.
if(!isset($_SESSION['questoes']))
{
	$_SESSION['questoes'] = array(); // Array de perguntas
	if (!$resultado = mysql_query("SELECT id FROM $db_questoes"))
		die('Erro ao obter as questões: ' . mysql_error());

	while($lin = mysql_fetch_row($resultado))
		array_push($_SESSION['questoes'], $lin[0]);

	shuffle($_SESSION['questoes']);
	reset($_SESSION['questoes']); // Começamos a percorrer pelo inicio do array

	$_SESSION['respostas'] = array();

	$_SESSION['atual'] = 0; // Posição no vetor de questões.
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>Teste</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css" />
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
      .sidebar-nav {
        padding: 9px 0;
      }
    </style>
    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap-responsive.min.css" />
    <link rel="stylesheet" type="text/css" href="questionario.css" />
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
	</head>
	<body class="container-fluid">
		<nav class="navbar navbar-fixed-top">
  		<div class="navbar-inner">
        <div class="container-fluid">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="#">Questionário teste</a>
        </div>
      </div>
    </nav>
    
		<article class="row-fluid">
		<section class="span8">
		<?php
		if($_POST['enviado'])
		{
			// Verifica por campos vazios
			if (!$_POST['resposta'] || !$_POST['comentario'])
			{
				// Variáveis para preencher o formulário
				$texto = htmlentities($_POST['comentario']);
				$sel = $_POST['resposta'];
			}
			else
			{
				/* Verifica se acertou a questão */
				$query = sprintf('SELECT resposta FROM %s WHERE id = %s', $db_questoes, $_SESSION['questoes'][$_SESSION['atual']]);
				if (!$resultado = mysql_query($query))
	    		die('Erro ao obter a questão anterior: ' . mysql_error());
	
				while($lin = mysql_fetch_row($resultado))
					$op_correta = $lin[0]; // vamos usar isso para direcionar os comentários exibidos
				
				// Para prevenir entradas duplas
				$entrada = array($_POST['resposta'] => $_POST['comentario']);
				if (!in_array($entrada, $_SESSION['respostas']))
				{
					array_push($_SESSION['respostas'], $entrada);
					$_SESSION['atual']++;
				}
			}
		}
	
		if ($_SESSION['atual'] < count($_SESSION['questoes']))
		{	
			// Inclusão da questão
			$query = sprintf('SELECT texto, resposta FROM %s WHERE id = %s', $db_questoes, $_SESSION['questoes'][$_SESSION['atual']]);
			if (!$resultado = mysql_query($query))
	    	die('Erro ao obter o enunciado: ' . mysql_error());
	
			while($lin = mysql_fetch_array($resultado))
			{
				$resposta = $lin['resposta'];
				echo $lin['texto'];
			}
			
	
			/* Os comentários direcionados */
			if (count($_SESSION['respostas']) > 0)
			{
				echo '<ul class="comentarios unstyled">';
				$opcao = key(end($_SESSION['respostas'])); // a escolha do usuário na questão anterior
				/* Vamos verificar se acertou */
				if ($opcao == $op_correta)
					$sinal = "<>";
				else
					$sinal = "=";
				
				/* Se acertou recebe comentários de quem errou e vice-versa */
				$query = sprintf('SELECT id, texto FROM %s WHERE questao = %d AND resposta %s %d ORDER BY id DESC LIMIT 3', $db_comentarios, $_SESSION['questoes'][$_SESSION['atual']], $sinal, $resposta);
				if (!$resultado = mysql_query($query))
					die('Erro ao obter os comentários: ' . mysql_error());
				
				echo '<br />';
				echo '<h4>Comentários</h4>';
				while($lin = mysql_fetch_array($resultado))
				{
					echo '<li>';
					printf('<div>%s</div>', $lin['texto']);
					echo '<ul>';
					echo '<li class="aval">';
					echo '<span class="inline btn-group" data-toggle="buttons-radio">';
					echo '<button class="disabled btn btn-info btn-mini">Avalie: </button><button class="aval-pos btn btn-mini"><i class="icon-thumbs-up"></i></button><button class="aval-neg btn btn-mini"><i class="icon-thumbs-down"></i></button>';
					echo '</span>';
					echo '</li>';
					printf('<li class="recoment hide" data-id="%s">', $lin['id']);
					echo '<textarea></textarea><br />';
					echo '<button class="btn btn-mini">Comentar</button></li>';
					echo '</ul>';
					echo '<br />';
					echo '</li>';
				}
	
				echo '</ul>';
			}
	
			/* Mostra o formulário */
			printf('<form action="%s" method="post">', $_SERVER['PHP_SELF']);
			echo "<fieldset>";

			/* As alternativas */
			$query = sprintf('SELECT id, alternativa FROM %s WHERE questao = %s', $db_respostas, $_SESSION['questoes'][$_SESSION['atual']]);		
			if (!$resultado = mysql_query($query))
				die('Erro ao obter as alternativas: ' . mysql_error());
			
			while($item = mysql_fetch_array($resultado))
			{
				printf('<label class="inline radio"><input class="" type="radio" name="resposta" value="%s" %s />%s</label>', $item['id'], $item['id'] == $sel ? 'checked' : '', $item['alternativa']);
			}

			/* E o espaço para comentários */
			echo '<div class="input">';
			printf('<textarea class="%s span5 xxlarge" name="comentario">%s</textarea>', $texto ? 'erro' : '', htmlentities($texto));
			echo "</div>";
			echo '<input class="btn btn-primary" type="submit" name="enviado" value="Enviar"/>';
			echo "</fieldset>";
			echo '</form>';
		}
		else
		/* Acabaram as questões e agora vamos gravar os resultados */			
		{
			for ($i = 0; $i < count($_SESSION['questoes']); $i++)
			{
				$query = sprintf('INSERT INTO %s (session_id, questao, resposta, texto, data) VALUES ("%s", %d, %d, "%s", NOW())', $db_comentarios, 
					session_id(), mysql_real_escape_string($_SESSION['questoes'][$i]), mysql_real_escape_string(key($_SESSION['respostas'][$i])), mysql_real_escape_string(current($_SESSION['respostas'][$i])));		
				if (!mysql_query($query))
					die('Erro ao gravar as respostas: ' . mysql_error());
			}
			
			session_regenerate_id();
			session_destroy();
	//		print_r($_SESSION['questoes']);
	//		print_r($_SESSION['respostas']);
			echo '<section class="well">';
			echo '<p>Sessão encerrada.</p>';
			printf ('<p><a class="btn btn-primary" href="%s">Recomeçar</a></p>', $_SERVER['PHP_SELF']);
			echo '</section>';
		}					
		?>
		</section>
		<script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="questionario.js"></script>
		<script type="text/javascript" src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.min.js"></script>
		<article>
	</body>
</html>
<?php
if ($link)
	mysql_close($link);
?>