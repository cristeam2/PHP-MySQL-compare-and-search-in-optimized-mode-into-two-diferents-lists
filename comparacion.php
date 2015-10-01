<?php

/*
Nota:
//Buscaremos  emails de entre diferentes listas de emailsOrganizados en la lista emails
//La busqueda esta optimizada para parar lo mas rapido posible
// se busca solo entre los emails que empiecen con la misma letra y dentro de esta se localiza el segundo caracter para buscar a partir de alli, ahorrando asi al maximo el proceso de la busqueda

*/

//Evitar muestre advertencia de mysql deprecated driver
error_reporting(E_ALL ^ E_DEPRECATED);


//Configurar la hora del servidor
date_default_timezone_set('Europe/Madrid');

//Carga de modulos php necesarios
include 'datosDeLaBBDD.php';


//Variables a usar
$duplicados=array();
$tienenPalabra=array();
$triplicados=array();

 //Creacion de los arrays de cada letra del abecedario 
 foreach($arrayLetrasAZ= range('a', 'z') as $letras) 
 {
      $emailsOrganizados[$letras]  = array();
 } 

//Mete cada email en el array correspondiente segun su letra inicial
 //Usar el ejmplo dado en el proyecto de PHP-How-to-connect-and-load-info-from-Mailchimp-API para cargar esta estructura
function organizaEmail($correo){
  global $emailsOrganizados;
  $encontrado=false;
  $letra=97; //= letra 'a'
  while (($letra <= 122) && (!$encontrado))  
  {
      if ($correo[0]==chr($letra))  
      {
          $emailsOrganizados[chr($letra)][] = $correo;
          $encontrado=true;
      }
      $letra++;      
  }
  if (!$encontrado)
  { 
    $emailsOrganizados['numericos'][] = $correo;            
  }
}




//Llamada a funcion que realiza los calculos
buscaEmailsEntreDiferentesListas();

//Buscaremos todos los emails de la lista de emailsOrganizados en la lista emails
//La busqueda esta optimizada para parar lo mas rapido posible
// se busca solo entre los emails que empiecen con la misma letra y dentro de esta se localiza el segundo caracter para buscar a partir de alli, ahorrando asi al maximo el proceso de la busqueda
function buscaEmailsEntreDiferentesListas()
{
 global $triplicados;
 global $emails;
 global $duplicados;
 global $TotalDeFilas;
 //  global $emailsOrganizados;

 echo "\nAnalizando...iniciado a ".date('d m Y H:i:s')."\n"; 
 for ($iteradorDeEmails=0; $iteradorDeEmails < $TotalDeFilas; $iteradorDeEmails++) 
 {

      //Comprobamos si existe
      //en esta calculo el nuevo iterador pero tambien tengo la funcion analizaSimple que es mas rapida ya que no lo calcula 
      if (!buscayDameNuevoIterador($iteradorDeEmails,$emails))
      {
            // Si no está, veo si tiene la palabra clave $palabra
            buscaEmpiezanConAlgunaPalabraDada($iterador,$array,$palabra);
       }
  } 

    //Buscamos emails duplicados y triplicados 
    //en triplicados  quito el segundo y siguientes, pero dejo el primero
    $duplicados=dameDuplicados($emails);
    $triplicados=dameEmailTriplicados();
    
  }

//Busco uno a uno los emails[iteradorDeEmails] dentro de la lista $emailsOrganizados
function buscayDameNuevoIterador($iteradorDeEmails,$emails)
{
  global $emailsOrganizados;

  $iteradorDeFuncion= 0;
  $numeroSalidaDeNumerico=0;
  $fin=0;
  $nuevoIndice=0;

  //Con esta variable los email que empiezen antes de la letra 'l', se buscaran en orden ascendetes y para lo que empiezen con una letra despues de l, se buscaran en orden inverso ( desde la z hasta la l)
  $letraDeCorteDeOrden='l';
  
  //Variable para saber por cual letra del abecedario (o numero ) empieza el email y asi ir al array correspondoente a esa letra o numero
  $char=$emails[$iteradorDeEmails][0];

  //Si es numerico se trata de forma diferente
  if (is_numeric($char)) 
  {
    $bFound=buscaNumerico($iteradorDeEmails,$emailsOrganizados,$emails,$numeroSalidaDeNumerico);
    $char="numericos";
  }
  else 
  {        
     //Dando por hecho que se organiza  de mayor a menor como mas alto el _
    if (($emails[$iteradorDeEmails][1] <="_" && !is_numeric($emails[$iteradorDeEmails][1]) && $emails[$iteradorDeEmails][1] != "." && $emails[$iteradorDeEmails][1] != "-" && $emails[$iteradorDeEmails][1] != "@"  ) || $emails[$iteradorDeEmails][1] > $letraDeCorteDeOrden ) 
    {    
      $bFound= finAinicio($iteradorDeEmails,$char,$emails,$emailsOrganizados,$fin);
    }
    else 
    { 
      $bFound= inicioAfin($iteradorDeFuncion,$char,$iteradorDeEmails,$emails,$emailsOrganizados);   
    }
  }

    if ($bFound)
    { 
      //Para saber el indice que corresponde en la estructura global y no en el email particionada por letras    
      if ($char=="numericos") 
      {
          $nuevoIndice=$numeroSalidaDeNumerico;
      } 
      else
      { 
         //Si no es numerico se suman la cantidad de email que hay hasta la letra inicial del email a tratar mas los email numericos ya que son los primeros siempre
         if ($emails[$iteradorDeEmails][1] > $letraDeCorteDeOrden || $emails[$iteradorDeEmails][1] =='_'  )
         {
              //De atras hacia inicio de (fin hacia la mitad), asi busco menos ya que corto en cierta letra media
              $nuevoIndice=transforma(($fin+1),$char);
         }
         else 
         {  
              //De  inicio hacia la mitad, asi busco menos ya que corto en cierta letra media
              $nuevoIndice=transforma($iteradorDeFuncion,$char)-1;
         }
      }
      //Aqui podria usar el nuevo indice
      //....$nuevoIndice...
    }
        
    return $bFound;
}


  //Funcion para iterar por todos los emails en esta lista
  //Busco uno a uno los emails[iteradorDeEmails] dentro de la lista $emailsOrganizados
function analizaSimple($iteradorDeEmails,$emails)
{
    global $emailsOrganizados;

    $iteradorDeFuncion=0;

    //Con esta variable los email que empiezen antes de la letra 'l', se buscaran en orden ascendetes y para lo que empiezen con una letra despues de l, se buscaran en orden inverso ( desde la z hasta la l)
    $letraDeCorteDeOrden='l';

    //Variable para saber por cual letra del abecedario (o numero ) empieza el email y asi ir al array correspondoente a esa letra o numero
    $char=$emails[$iteradorDeEmails][0];

    if (is_numeric($char)) 
    {
      $bFound=buscaNumerico($iteradorDeEmails,$emailsOrganizados,$emails,$numeroSalidaDeNumericoV);

    }
    else 
    {        
     //Dando por hecho que se organiza en de mayor a menor como mas alto el _
     if (($emails[$iteradorDeEmails][1] <="_" && !is_numeric($emails[$iteradorDeEmails][1]) && $emails[$iteradorDeEmails][1] != "." && $emails[$iteradorDeEmails][1] != "-" && $emails[$iteradorDeEmails][1] != "@") || $emails[$iteradorDeEmails][1] > $letraDeCorteDeOrden ) 
     { 
       $bFound= finAinicio($iteradorDeEmails,$char,$emails,$emailsOrganizados,$finV); 
     }
     else 
     { 
       $bFound= inicioAfin($iteradorDeFuncion,$char,$iteradorDeEmails,$emails,$emailsOrganizados); 
     }
     if ($bFound) 
     {
     //Aqui podria hacer uso del id de los no encontrados:
     // $noEncontrados[] = $iteradorDeEmails;
    } 
  }
  return $bFound;
}



//Busca en las estructuras pasadas a $palabra 
function buscaEmpiezanConAlgunaPalabraDada($iterador,$array,$palabra){

  global $tienenPalabra;
  if ((substr($array[$iterador], 0, 5)) == $palabra ) 
  {
      $tienenPalabra=$iterador;
  }
}


//Devuelve solo duplicados
function dameDuplicados( $array ) 
{
  return array_unique( array_diff_assoc( $array, array_unique( $array ) ) );
}



//Funcion para buscar los duplicados, triplicados o 'x' multiplicados en genereal
function dameEmailTriplicados()
{
      global      $servername ;
      global      $username ;
      global      $password ;
      global      $dbname ;
      global      $tabla;

      $arrayDeDuplicados=array(); 
      $conn = mysql_pconnect($servername, $username, $password);
      mysql_select_db($dbname, $conn) ;
      $sql = "SELECT  a.idCliente, a.Email FROM $tabla a 
                    INNER JOIN ( SELECT  Email, COUNT(*) totalCount FROM $tabla 
                        GROUP   BY Email ) b ON a.Email = b.Email WHERE   b.totalCount >= 2;";
      $resultado = mysql_query($sql);
      if (!$resultado) 
      {
        $message  = 'Invalid query: ' . mysql_error() . "\n";
        $message .= 'Whole query: ' . $sql;
        die($message);
      }
      while ($row = mysql_fetch_assoc($resultado))
      {
        $arrayDeDuplicados[]=$row;
      }
      mysql_close($conn);
      return calculaMaximosId($arrayDeDuplicados);
}


//Devuelve en un array los id maximos de los email repetidos
function calculaMaximosId($arrayDeDuplicados)
{
  $maximosId=array();
  $total=count($arrayDeDuplicados);

  for ($i=0; $i <$total ; $i++) 
  {  
    $j=$i+1;
    $encontado=False;
    while ( $j < $total && !$encontado ) 
    {
       if ($arrayDeDuplicados[$i]['Email']==$arrayDeDuplicados[$j]['Email'] )
       {
            $maximosId[$i]=max($arrayDeDuplicados[$i]['idCliente'],$arrayDeDuplicados[$j]['idCliente']);
            $encontado=True;
       }
       $j++;
    }
  }
  return $maximosId;
}




//Busca en que posicion esta el email dentro de los emails numericos, y lo devuelve en $numeroSalidaDeNumerico
function buscaNumerico($iterador,$emailsOrganizados,$listaEmails,&$numeroSalidaDeNumerico)
{
  foreach ($emailsOrganizados['numericos'] as $email) 
  {
    if ($listaEmails[$iterador]==$email) 
    {
      return true;
    }
    $numeroSalidaDeNumerico++;
  }
  return false;
}




//Buscar los elementos en orden ascendete hacia el ultimo, asi para los email que esten cerca de las primera letras del abcedario resultara especialmente optimo
//iTeradorInterno=Para interar solo dentro de los emails organizados, lo devuelvo para luego poder saber su posicion relativa en la lista general y no la organizada
//$char= variable para saber cual es el primer caracter del email 
//iExternoFor= iterador general al mas alto nivel
//listaEmails=La lista general de todos los email sin organizar 
//emailsOrganizados= estructura con los email organizados segun la primera letra del abecedario
function inicioAfin(&$iTeradorInterno,$char,$iExternoFor,$listaEmails,$emailsOrganizados)
{
 $bFound=   False;
 $salir =   False;
 $fin=count($emailsOrganizados[$char]);
 while ((!$bFound) && (!$salir) && $iTeradorInterno < $fin )
 { 
  if ($listaEmails[$iExternoFor] == $emailsOrganizados[$char][$iTeradorInterno])
  {

    $bFound = True;
  }
  //Salimos si ya se ha superado la letra siguiente a la del primer email a analizar, ya que se supone no existira, ejemplo si busco 'ana' cuando llegue a 'am' salgo
  $salir=(!$bFound && ($emailsOrganizados[$char][$iTeradorInterno][1] > $listaEmails[$iExternoFor][1])  ) ;
  $iTeradorInterno++;
 }
 return $bFound;
}




//Buscar los elementos de atras hacia adelante, asi para los email que esten cerca de las ultimas letras del abcedario resultara especialmente optimo
function finAinicio($iExternoFor,$char,$listaEmails,$emailsOrganizados,&$fin)
{
  $bFound=   False;
  $salir =   False;

  $fin=count($emailsOrganizados[$char]);
  
  //Para poder acceder a la ultima posicion del array correcta
  $fin--;
  while ((!$bFound) && (!$salir) && $fin >= 0 )
  {
   if ($listaEmails[$iExternoFor] == $emailsOrganizados[$char][$fin])
   {

     $bFound = True; 
   }
   if  ( !$bFound && ($listaEmails[$iExternoFor][1]=="_") &&  ($emailsOrganizados[$char][$fin][1] > $listaEmails[$iExternoFor][1])  ) 
   {
     $salir = True;           
   }
   else 
   {
     if  ( !$bFound && ($emailsOrganizados[$char][$fin][1] != "_") && ($emailsOrganizados[$char][$fin][1] < $listaEmails[$iExternoFor][1])  ) 
     {
        $salir = True;           
     }
   }
   $fin--;
  }
  return $bFound;
}

?>  