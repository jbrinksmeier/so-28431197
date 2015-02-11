<?php
/**
 * Goal of this script is to read a csv-file with a primary-key (input-primary.csv)
 * in field 0 and join contents from a second csv-file (input-detail.csv).
 * Each row in input-detail.csv has the primary key from input-primary.csv
 * in field 0 as well.
 * This script needs php version 5.4 o higher
 */
 
/**
 * First, we define some helper functions
 */

/**
 * Read csv-contents from $filename and return it indexed by primary-key.
 * Primary-key is in field 0
 * 
 * @param string $filename file to read
 * @return array
 */
function getCsvContentIndexedByPrimaryKey($filename)
{
	$handle = fopen($filename, 'r');
	$indexedContents = [];
	while (false !== $row = fgetcsv($handle)) {
		$primaryKey = $row[0];
		$indexedContents[$primaryKey] = $row;
	}
	
	return $indexedContents;
}

/**
 * Joins contents from $row and $indexedContents by index taken from 
 * field 0 of $row. Primarykey-field of $row will be unset. If no content
 * was found in $indexedContents an exception is thrown with the primary-key.
 * 
 * @param array $row row from input-detail.csv
 * @param array $indexContents result from getCsvContentIndexedByPrimaryKey
 * @return array joined content
 * @throws Exception if no content for $row[0] was found in $indexedContents
 */
function joinRowByPrimaryKey($row, $indexedContents)
{
	$primaryKey = $row[0];
	if (isset($indexedContents[$primaryKey])) {
		$contentToJoin = $indexedContents[$primaryKey]; unset($row[0
		]); return array_merge($contentToJoin, $row);
	}
	throw new \Exception(sprintf(
		'Primary-key %s not found in indexed-contents', $row[0]));
}

/**
 * Now, here we go.
 */

// we create the indexed-content and initialize our output and error-handling
$indexedContents = getCsvContentIndexedByPrimaryKey('input-primary.csv');
$outputContent = [];
$errors = [];

// now we read the second csv-file
$handle = fopen('input-detail.csv', 'r');
while (false !== $row = fgetcsv($handle)) {
	try {
		$outputContent[] = joinRowByPrimaryKey($row, $indexedContents);
	} catch (\Exception $e) { // we catch the exception from joinRowByPrimaryKey here
		$errors[$row[0]] = $e->getMessage();
	}
}

// Finally, we create our result-file and write our output-content to it
// note the usage of fputcsv @see http://php.net/fputcsv
// there is no need to manually write commas, line-endings and the like
$handle = fopen('result.csv', 'w');
foreach ($outputContent as $row) {
	fputcsv($handle, $row);
}

// and print our errors
foreach ($errors as $error) {
	echo $error . PHP_EOL;
}
