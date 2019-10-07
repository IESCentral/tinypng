<?php

class tinypng 
{
	private $api_key;

	public function __construct($key) 
	{
		if ($key == "") 
		{
			throw new Exception("The correct way to call tinypng class is with tinypng(<<tinypng api key>>)");
		}	
		$this->api_key = $key;
		\Tinify\setKey($this->api_key);
		\Tinify\validate();
	}

	public function tinify ($image_file, $backup_folder) 
	{
		if (trim(shell_exec(sprintf("which %s", escapeshellarg("identify")))) == "") 
		{
			throw new Exception("ImageMagick does not appear to be installed on this server.  Program \"identify\" not found");
		}
		$image_path_info = pathinfo($image_file);
        $image_filename = $image_path_info["basename"];
		$image_filename_no_extension = $image_path_info['filename'];
		$image_extension = $image_path_info["extension"];
		$backup_folder = rtrim($backup_folder, '/') . '/';
		$sourceData = file_get_contents($image_file);
		$resultData = \Tinify\fromBuffer($sourceData)->toFile("/tmp/" . $image_filename);
        $imageTest = exec("identify -format \"%f\" \"/tmp/\"" . $image_filename . " 2>&1");
		if (trim($imageTest) != $image_filename) 
		{
			throw new Exception("Image file returned from TinyPNG was corrupt or invalid: /tmp/$image_filename from source: $image_file");  
		}
        system("mkdir -p " . $backup_folder);
        if (file_exists($backup_folder . $image_filename)) 
		{
			rename($backup_folder . $image_filename, $backup_folder . $image_filename_no_extension . "_" . date("Ymd_His") . "." . $image_extension);
		}
		rename($image_file, $backup_folder . $image_filename);
		rename("/tmp/".$image_filename, $image_file);
		return true;
	}
}
