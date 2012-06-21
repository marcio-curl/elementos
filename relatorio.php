<?php
ini_set('default_charset','UTF-8');

// Configuração do MySQL
require("mysql.inc.php");
$link = mysql_connect($mysql_host, $mysql_user, $mysql_password) or die('Erro ao conectar o MySQL: ' . mysql_error());
mysql_select_db($mysql_database) or die('Erro ao conectar o banco de dados');	
mysql_set_charset('utf8');
mysql_query('SET character_set_results=utf8');

/* Em que tabela estão os comentários */
$db_questoes = 'questoes';
$db_respostas = 'respostas';
$db_comentarios = 'comentarios';
$db_recomentarios = 'recomentarios';

function intervalo($data)
{		
	$tempo = abs(time() - strtotime($data));
	
	return $tempo;
}

/* Modo de avaliação */
if ($_GET['prof'] === "1")
	$admin = true;
?>
<!DOCTYPE html>

<html>
  <head>
    <meta charset="UTF-8" />
    <title>Relatório</title>
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
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
		<link rel="stylesheet" type="text/css" href="questionario.css" />
		<script type="text/javascript" src="relatorio.js"></script>
  </head>
  <body>
	  <nav class="navbar navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container-fluid">
					<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</a>
					<a class="brand" href="#">Relatório</a>
				</div>
			</div>
	    </nav>
		<article class="container-fluid">
			<table class="table table-striped">
				<thead>
					<tr><th>id</th><th colspan="2">Data</th></tr>
				</thead>
				<tbody>
				<?php
					/* Armazenamento das questões e respostas no vetor $questoes */
					if (!$resultado = mysql_query("SELECT * FROM $db_questoes"))
						die('Erro ao obter as questões' . mysql_error());
					
					while($lin = mysql_fetch_array($resultado))
					{
						$questoes[$lin['id']]['titulo'] = $lin['titulo']; 
						$questoes[$lin['id']]['texto'] = $lin['texto']; 
//						$questoes[$lin['id']]['resposta'] = $lin['resposta']; 
					}
	
			
					/* Já aqui guardamos o enunciado das respostas */
					if (!$resultado = mysql_query("SELECT * FROM $db_respostas"))
						die('Erro ao obter as respostas' . mysql_error());
					
					while($lin = mysql_fetch_array($resultado))
					{
						$questoes[$lin['questao']]['alternativa'][$lin['id']] = $lin['alternativa']; 
					}
					
	
					/* E agora o vetor $entrada com as respostas dos usuários */
					if (!$resultado = mysql_query("SELECT * FROM $db_comentarios ORDER BY id DESC"))
						die('Erro ao obter as questões' . mysql_error());
	
					while($lin = mysql_fetch_array($resultado))
					{
						$entrada[$lin['session_id']]['data'] = $lin['data'];
						$entrada[$lin['session_id']]['resp'][$lin['id']] = array('questao' => $lin['questao'], 'resposta' => $lin['resposta'], 'texto' => $lin['texto']);
					}
	
	
					/* E agora guardamos em $entrada os comentários feitos pelo usuário */
					if (!$resultado = mysql_query("SELECT * FROM $db_recomentarios"))
						die('Erro ao obter os comentários' . mysql_error());
	
					while($lin = mysql_fetch_array($resultado))
					{
						$entrada[$lin['session_id']]['comentario'][$lin['referencia']] = array('avaliacao' => $lin['avaliacao'], 'texto' => $lin['texto']);
					}
	
					/* E finalmente as respostas aos comentários */
	/*				if (!$resultado = mysql_query("SELECT * FROM $db_recomentarios"))
						die('Erro ao obter os comentários' . mysql_error());				
					
					while($lin = mysql_fetch_array($resultado))
					{
						// Isso poderia ser implementado melhor...
						$comentarios_usuario[$lin['session_id']][] = array('referencia' => $lin['referencia'], 'avaliacao' => $lin['avaliacao'], 'texto' => $lin['texto']); 
						$respostas[$lin['referencia']]['comentarios'][] = array('avaliacao' => $lin['avaliacao'], 'texto' => $lin['texto']);
					}*/
	
					foreach($entrada as $i => $item)
					{
						echo "<tr>";
						printf('<td><a data-toggle="modal" href="#modal%s">%s</a></td><td>%s</td>', $i, substr($i, 0, 10) . '...', date(DATE_RFC2822, strtotime($item['data'])));
						
						echo "<td>";
						printf('<section class="hide modal" id="modal%s">', $i);
						echo '<div class="modal-header">';
						echo '<button type="button" class="close" data-dismiss="modal">×</button>';
						printf('<h3>%s</h3>', date(DATE_RFC2822, strtotime($item['data'])));
						echo '</div>';
						echo '<div class="modal-body">';
						foreach ($item['resp'] as $ref => $resp)
						{
							echo '<dl class="span7">';
							printf('<dt>%s: <span class="label label-inverse">%s</span></dt>', $questoes[$resp['questao']]['titulo'], $questoes[$resp['questao']]['alternativa'][$resp['resposta']]);
							printf('<dd>%s</dd>', $resp['texto']);
	
							// Procura os comentários relativos a resposta
							array_walk($entrada, create_function('&$v, $key, $ref', 'if(is_array($v["comentario"]) && array_key_exists($ref, $v["comentario"])) echo "<dd class=\"span2 offset1\">" . $v["comentario"][$ref]["texto"] . "</dd>";'), $ref);

							if ($admin)
								echo '<dd class="aval span5"><textarea></textarea><br /><button class="btn btn-mini">Avaliar</button></dd>';

							echo "</dl>";
						}
	
						if (is_array($item['comentario']))
						{
							echo '<h3>Comentários do usuário</h3>';
							echo '<ul class="row unstyled">';
	
							foreach ($item['comentario'] as $ref => $coment)
							{
								echo '<li>';
//								$ref = key($item['comentario']);
	
								// E aqui buscamos a resposta de referência.
//								echo '<div>';
								array_walk($entrada, create_function('&$v, $key, $ref', 'if(is_array($v["resp"]) && array_key_exists($ref, $v["resp"])) echo $v["resp"][$ref]["texto"];'), $ref);
//								echo '</div>';
								
								// E o comentário feito
								echo '<ul class="">';
								printf('<li><i class="icon-thumbs-%s"></i>: %s</li>', $coment['avaliacao'] == -1 ? 'down' : 'up', $coment['texto']);

								if ($admin)
								{
									echo '<li class="comentComent">';
									echo '<a class="avalComent btn btn-mini" href="#"><i class="icon-comment" title="Comentar"></i></a><a class="sinaliza btn btn-mini" data-toggle="button" href="#"><i class="icon-flag" title="Sinalizar"></i></a><br />';
									echo '<textarea class="hide"></textarea><br />';
									echo '<button class="btn btn-mini hide">Comentar</button>';
									echo '</li>';									
								}

								echo '</ul><br />';
								echo '</li>';
							}
							echo '</ul>';
						}
	
						echo "</div>"; // Corpo do Modal
						echo '</section>'; // Modal
						echo "</td>";
						echo "</tr>";
					}
		//			print_r($questoes);
		//			print_r($entrada);
				?>
				</tbody>
			</table>
		</article>	
		<script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
		<!-- Não incluir o bootstrap-modal.js -->
  </body>
</html>