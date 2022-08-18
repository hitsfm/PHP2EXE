<?php



ini_set('display_errors','off');

if($fp=fsockopen('127.0.0.1', 80,$errno,$errstr,1))
{
 fclose($fp);
 $o = new COM('WScript.Shell');
 $o->Popup('Kuirk can\'t start because Web port (80) is already used.',15,'Kuirk',48);
}
else
{
 // lit le fichier INI s'il existe
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
 $a=str_replace("\\", "/",realpath('.'));  // "

 // modification des configurations PHP/APACHE/MYSQL
 $tab=array(array('./defaultconfig/php.ini', './apache/php.ini'),array('./defaultconfig/httpd.conf', './apache/conf/httpd.conf'));
 if(is_dir('mysql')) $tab[]=array('./defaultconfig/my.ini', './mysql/my.ini');
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
 $oa = new COM('WScript.Shell');
 $ob = $oa->Run('apache\\apache.exe -k start', 0, false);

  // démarre mysql
 if(is_dir('mysql'))
 {
  $oa2 = new COM('WScript.Shell');
  $ob2 = $oa2->Run('mysql\\bin\\mysqld.exe', 0, false);
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
 $ob43 = $oa4->Run('apache\\apache.exe -k shutdown',0, true);
 if(is_dir('mysql'))
 {
  $oa5 = new COM('WScript.Shell');
  $ob5 = $oa5->Run('mysql\\bin\\mysqladmin.exe -u root shutdown', 0, true);
 }

}


?>
