<?php
/**
* package.php
* Create a Zend Framework phar
*
* @author Cal Evans <cal@calevans.com>
* @author John Douglass <john .douglass@oit.gatech.edu>
*/
 
$getOptLongArray = array("stub:");
$getOptParams    = "s:p:v";
$options         = getOpt($getOptParams,$getOptLongArray);
 
if(!isset($options['s'],$options['p']))
{
    echo "You did not specify either a path or a phar file name.\n";
    displayHelp();
    die(1);
}
 
/*
 * Set up our environment
 */
$sourceLocation = $options['s'];
$basePointer    = strpos($options['s'],'Zend');
$pharFile       = $options['p'];
 
/*
 * Make sure things are sane before progressing
 */
if ($basePointer<1) {
    echo "It looks like your path is not a Zend Framework path.\nPlease check and try again.\n";
    displayhelp();
    die(1);
}
 
// At this point, we need to check to see if the file exists. If neither exist, throw exception.
if (isset($options['stub'])) {
    $stubFile = $options['stub'];
} else {
    $stubFile = 'stub.php';
}
 
if(!file_exists($sourceLocation))
{
 echo "ERROR: Source file location does not exist!\nCheck your source and try again.\n";
    displayhelp();
    die(1); 
}
 
/*
* Let the user know what is going on
*/
echo "Creating PHAR\n";
echo "Source      : {$sourceLocation}\n";
echo "Destination : {$pharFile}\n";
echo "Stub File   : {$stubFile}\n\n";
 
/*
* Clean up from previous runs
*/
if (file_exists($pharFile)) {
  Phar::unlinkArchive($pharFile);
}
 
/*
* Setup the phar
*/
$p = new Phar($pharFile, 0, $pharFile);
$p->compressFiles(Phar::GZ);
$p->setSignatureAlgorithm (Phar::SHA1);
 
/*
* Now build the array of files to be in the phar.
* The first file is the stub file. The rest of the files are built from the directory.
*/
$files = array();
$files["stub.php"] = $stubFile;
 
echo "Building the array of files to include.\n";
 
 
$rd = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceLocation));
foreach($rd as $file) {
 if (strpos($file->getPath(),'.svn')===false &&
      $file->getFilename() != '..' &&
      $file->getFilename() != '.')
      {
         $fileIndex = substr($file->getPath().DIRECTORY_SEPARATOR.$file->getFilename(),$basePointer);
         $fileName = $file->getPath().DIRECTORY_SEPARATOR.$file->getFilename();
         $files[$fileIndex] = $fileName;
         // Coined "phindex" to refer to the included index pointing to the real filename on disk we are creating
         if (isset($options['v'])) {
            echo "   PHIndex[{$fileIndex}] = {$fileName}\n";            
         } // if (isset($options['v']))
      }
} // foreach($rd as $file)
 
echo "Now building the phar.\n";
 
/*
* Now build the archive.
*/
$p->startBuffering();
$p->buildFromIterator(new ArrayIterator($files));
$p->stopBuffering();
 
/*
* finish up.
*/
$p->setStub($p->createDefaultStub("stub.php"));
$p = null;
 
if (isset($options['v'])) {
    echo count($files)." files Added to ".$pharFile."\n";
} // if (isset($options['v']))
 
echo "Done.\n";
exit;
 
function displayHelp()
{
    echo "\n\npachage.php\n";
    echo "  Authors: Cal Evans, John Douglass\n\n";
    echo "  -s The directory where Zend Framework is located. Must end in /Zend. \n";
    echo "  -p The name to give your phar file.\n";
    echo "  --stub The name of your stub file. Will default to stub.php if not passed in.\n";
    echo "  -v verbose mode.\n";
}
