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
			'submissions' => array (
				'description' => 'Submissions',
				'url' => 'submissions/',
				'tab' => 'Submissions',
				'icon' => 'page_white_stack',
				'administrator' => true,
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
 			  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Automatic key',
 			  `username` varchar(10) NOT NULL COMMENT 'Username',
 			  `title` varchar(255) NOT NULL COMMENT 'Title',
 			  `amount` decimal(9,2) NOT NULL COMMENT 'Amount requested',
 			  `description` text NOT NULL COMMENT 'Short justification of equipment requested',
 			  `purpose` enum('','Teaching','Fieldwork (including student dissertations)','Laboratory work','Health, safety or security purposes','Other') NOT NULL COMMENT 'This equipment will be used primarily for',
 			  `item1Description` varchar(255) NOT NULL COMMENT 'Item #1 description',
 			  `item1Amount` decimal(9,2) NOT NULL COMMENT 'Item #1 unit price',
 			  `item1Quantity` int NOT NULL COMMENT 'Item #1 quantity',
 			  `item2Description` varchar(255) DEFAULT NULL COMMENT 'Item #2 description',
 			  `item2Amount` decimal(9,2) DEFAULT NULL COMMENT 'Item #2 unit price',
 			  `item2Quantity` int DEFAULT NULL COMMENT 'Item #2 quantity',
 			  `item3Description` varchar(255) DEFAULT NULL COMMENT 'Item #3 description',
 			  `item3Amount` decimal(9,2) DEFAULT NULL COMMENT 'Item #3 unit price',
 			  `item3Quantity` int DEFAULT NULL COMMENT 'Item #3 quantity',
 			  `item4Description` varchar(255) DEFAULT NULL COMMENT 'Item #4 description',
 			  `item4Amount` decimal(9,2) DEFAULT NULL COMMENT 'Item #4 unit price',
 			  `item4Quantity` int DEFAULT NULL COMMENT 'Item #4 quantity',
 			  `item5Description` varchar(255) DEFAULT NULL COMMENT 'Item #5 description',
 			  `item5Amount` decimal(9,2) DEFAULT NULL COMMENT 'Item #5 unit price',
 			  `item5Quantity` int DEFAULT NULL COMMENT 'Item #5 quantity',
 			  `itemsAdditional` text COMMENT 'If you have more than 5 items and/or cannot simplify your request into 5 lines, please paste in the rows/columns here from your spreadsheet.',
 			  `comments` text COMMENT 'Are there any additional details you would like to include (e.g. website links, available discounts, lead times on particular items)?',
 			  `updatedAt` datetime NOT NULL COMMENT 'Updated at',
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
	
	
	# Submissions
	public function submissions ()
	{
		# Start the HTML
		$html = '';
		
		# Get dataBinding attributes
		$dataBindingAttributes = $this->submissionsDataBindingAttributes ();
		
		# Define general sinenomine settings
		$sinenomineExtraSettings = array (
			'submitButtonPosition' => 'bottom',
			'fieldFiltering' => false,
			'intelligence' => true,
		);
		
		# Delegate to the standard function for editing
		$html .= $this->editingTable (__FUNCTION__, $dataBindingAttributes, 'ultimateform horizontalonly', false, $sinenomineExtraSettings);
		
		# Show the HTML
		echo $html;
	}
	
	
	# Databinding attributes for submissions
	private function submissionsDataBindingAttributes ()
	{
		# Set the databinding attributes
		$dataBindingAttributes = array (
			
		);
		
		# Return the attributes list
		return $dataBindingAttributes;
	}
}

?>
