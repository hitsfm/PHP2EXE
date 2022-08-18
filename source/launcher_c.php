<?php


ini_set('display_errors','off');
 
if(!$_ENV['TEMP']) $_ENV['TEMP']='c:/temp/';
 
 $file=str_replace('.exe','',basename($argv[0]));
 $file=str_replace('.php','',$file);
 $file.='.kui';

 $o = new COM('WScript.Shell');
 if(!file_exists($file))
 {
  $o->Popup($file.' not found !',15,'Kuirk',48);
  die();
 }
 else
 {
  if(!is_dir($_ENV['TEMP'])) mkdir($_ENV['TEMP']);
  if(!is_dir($_ENV['TEMP'].'/kuirk/')) mkdir($_ENV['TEMP'].'/kuirk/');

  zasUnBlend($file,$_ENV['TEMP'].'/kuirk/');
  mkdir($_ENV['TEMP'].'/kuirk/tmp');

  //ajout des fichier mysql manquant (à 0 octets)
  $tab=array('columns_priv.MYD','func.MYD','host.MYD','tables_priv.MYD');
  foreach($tab as $t)
  {
   if(!is_file($_ENV['TEMP'].'/kuirk/mysql/data/mysql/'.$t))
   {
    $fp=fopen($_ENV['TEMP'].'/kuirk/mysql/data/mysql/'.$t,'w+');
    fclose($fp);
   }
  }




 if($fp=fsockopen('127.0.0.1', 80,$errno,$errstr,1))
 {
  fclose($fp);
  $o = new COM('WScript.Shell');
  $o->Popup('Kuirk can\'t start because Web port (80) is already used.',15,'Kuirk',48);
 }
 else
 {
  $file=file('KuirK.ini');
  if($file) foreach($file as $f)
  {
   $f=trim($f);
   if($f&&$f{0}!=';'&&$f{0}!='[')
   {
    $tab=explode('=', $f);
    $ini[trim($tab[0])]=trim($tab[1]);
   }
  }
  else
  {
   $ini[browser]='C:/PROGRA~1/INTERN~1/IEXPLORE.EXE';
   $ini[fullscreen]='off';
  }

  $navigateur=str_replace('/', "\\", $ini[browser]);  // "
  $pleinecran=$ini[fullscreen];

  // préparation de la configuration
  $a=str_replace("\\", "/",realpath($_ENV['TEMP'].'/kuirk/'));  // "

  // modification des configurations PHP/APACHE/MYSQL
  $tab=array(array($_ENV['TEMP'].'/kuirk/defaultconfig/php.ini', $_ENV['TEMP'].'/kuirk/apache/php.ini'),array($_ENV['TEMP'].'/kuirk/defaultconfig/httpd.conf', $_ENV['TEMP'].'/kuirk/apache/conf/httpd.conf'));
  if(is_dir($_ENV['TEMP'].'/kuirk/mysql')) $tab[]=array($_ENV['TEMP'].'/kuirk/defaultconfig/my.ini', $_ENV['TEMP'].'/kuirk/mysql/my.ini');
  foreach($tab as $t)
  {
   $fp=fopen($t[0],'r');
   $file=str_replace('#path#',$a,fread($fp, filesize($t[0])));
   fclose($fp);
   $fp=fopen($t[1], 'w+');
   fwrite($fp,$file);
   fclose($fp);
  }


  // démarre apache
  $tmp=str_replace('/','\\',$_ENV['TEMP']); //'
  $oa = new COM('WScript.Shell');
  $ob = $oa->Run($tmp.'\\kuirk\\apache\\apache.exe -k start', 0, false);

  // démarre mysql
  if(is_dir($_ENV['TEMP'].'/kuirk/mysql'))
  {
   $oa2 = new COM('WScript.Shell');
   $ob2 = $oa2->Run($tmp.'\\kuirk\\mysql\\bin\\mysqld.exe', 0, false);
  }

  sleep(3);

  // ouvre le navigateur
  $p=1;
  if($pleinecran=='on'||$pleinecran=='1')
  {
   if(ereg('IEXPLORE.EXE', $navigateur)) $navigateur.=' -k';
   $p=3;
  }
  $oa3 = new COM('WScript.Shell');
  $ob3 = $oa3->Run($navigateur.' http://localhost', $p, true);


  // ICI LE PROGRAMME ATTEND LA FERMETURE De IE


  // stop toutes les appplications serveur.
  $oa4 = new COM('WScript.Shell');
  $ob43 = $oa4->Run($tmp.'\\kuirk\\apache\\apache.exe -k shutdown',0, true);
  if(is_dir($_ENV['TEMP'].'/kuirk/mysql'))
  {
   $oa5 = new COM('WScript.Shell');
   $ob5 = $oa5->Run($tmp.'\\kuirk\\mysql\\bin\\mysqladmin.exe -u root shutdown', 0, true);
  }

  sleep(5);
 }

  // efface le temp
  remove_directory($_ENV['TEMP'].'/kuirk/');

 }



function zasUnBlend($filename,$dest='',$file='')
{
 $r=1;

 if(!is_dir($_ENV['TEMP'].'/')) mkdir('temp/') or $r=0;

 $fp=fopen($_ENV['TEMP'].'/temp_z.tmp','wb');
 $fz=gzopen($filename,'rb');
 if(!$fz)
 {
  echo 'ZAS ERROR : filename not found !'."\n";
  $r=0;
 }
 while(!gzeof($fz)) fwrite($fp,gzread($fz,10000));
 gzclose($fz);
 fclose($fp);

 if($dest&&!is_dir($dest)) mkdir($dest) or $r=0;
 $fp=fopen($_ENV['TEMP'].'/temp_z.tmp','r');
 $n=trim(fread($fp,10));
 $liste=explode("\n",fread($fp,$n-10));
 fclose($fp);
 $fp=fopen($_ENV['TEMP'].'/temp_z.tmp','r');
 $last=$n;
 foreach($liste as $l)
 if($l)
 {
  $l0=$l;
  if($l{1}==':') list($a,$l)=explode(':',$l);
  list($a,$b)=explode("\t",$l);
  list($a0,$b0)=explode("\t",$l0);
  if(!$file||$file==$a0)
  {
   $tab=explode('/',dirname($a));
   $test=$dest;
   foreach($tab as $t)
   if($t)
   {
    $test.='/'.$t;
    if(!is_dir($test)) mkdir($test);
   }
   $fp2=fopen($dest.'/'.$a,'w+b');
   fseek($fp,$last,SEEK_SET);
   fwrite($fp2,fread($fp,$b));
   fclose($fp2);
   if($file) break;
  }
  $last+=$b;
 }
 fclose($fp);
 unlink($_ENV['TEMP'].'/temp_z.tmp');
 return($r);
}



function remove_directory($dir)
{
 if ($handle = opendir($dir))
 {
  while(false!==($item=readdir($handle)))
  {
   if ($item!='.'&&$item!='..')
   {
    if (is_dir($dir.'/'.$item)) remove_directory($dir.'/'.$item);
    else unlink($dir.'/'.$item);
   }
  }
  closedir($handle);
  rmdir($dir);
 }
}



?>
