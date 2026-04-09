<?php

# Class to create a template application
class labEquipmentGrants extends frontControllerApplication
{
	# Function to assign defaults additional to the general application defaults
	public function defaults ()
	{
		# Specify available arguments as defaults or as NULL (to represent a required argument)
		$defaults = array (
			'applicationName'		=> 'Small equipment grant applications',
			'div'					=> strtolower (__CLASS__),
			'tabUlClass'			=> 'tabsflat',
			'databaseStrictWhere'	=> true,
			'nativeTypes'			=> true,
			'administrators'		=> 'administrators',
			'database'				=> 'labequipmentgrants',
			'table'					=> 'submissions',
		);
		
		# Return the defaults
		return $defaults;
	}
	
	
	# Function to assign supported actions
	public function actions ()
	{
		# Define available actions
		$actions = array (
			'someaction' => array (
				'description' => 'Do some action',
				'url' => 'someaction/',
				'tab' => 'Some action',
				'icon' => 'add',
			),
		);
		
		# Return the actions
		return $actions;
	}
	
	
	# Database structure definition
	public function databaseStructure ()
	{
		return "
			
			-- Administrators
			CREATE TABLE IF NOT EXISTS `administrators` (
			  `username` VARCHAR(255) NOT NULL COMMENT 'Username' PRIMARY KEY,
			  `active` ENUM('','Yes','No') NOT NULL DEFAULT 'Yes' COMMENT 'Currently active?',
			  `privilege` ENUM('Administrator','Restricted administrator') NOT NULL DEFAULT 'Administrator' COMMENT 'Administrator level'
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='System administrators';
			
			-- Settings
			CREATE TABLE IF NOT EXISTS `settings` (
			  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'Automatic key (ignored)' PRIMARY KEY,
			  `somesetting` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Some setting'
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Settings';
			INSERT INTO settings (id) VALUES (1);
			
			-- My table
			CREATE TABLE IF NOT EXISTS `submissions` (
			  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Automatic key',
			  `updatedAt` DATETIME NOT NULL COMMENT 'Updated at',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='My table';
		";
	}
	
	
	
	# Additional processing
	public function main ()
	{
		
	}
	
	
	
	# Home page
	public function home ()
	{
		//
		$html = '<p>' . __FUNCTION__ . '</p>';
		
		# Show the HTML
		echo $html;
	}
	
	
	# Function to do some action
	public function someaction ()
	{
		//
		$html = '<p>' . __FUNCTION__ . '</p>';
		
		# Show the HTML
		echo $html;
	}
}

?>
