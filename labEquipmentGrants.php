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
			'recipientEmail'		=> NULL,
		);
		
		# Return the defaults
		return $defaults;
	}
	
	
	# Function to assign supported actions
	public function actions ()
	{
		# Define available actions
		$actions = array (
			'apply' => array (
				'description' => 'Apply for a grant',
				'url' => 'apply/',
				'tab' => 'Apply',
				'icon' => 'add',
				'authentication' => true,
			),
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
 			  `title` varchar(255) NOT NULL COMMENT 'Title',
 			  `email` varchar(255) NOT NULL COMMENT 'E-mail',
 			  `amount` decimal(6,2) NOT NULL COMMENT 'Amount requested (including VAT)',
 			  `description` text NOT NULL COMMENT 'Short justification of equipment requested',
 			  `purpose` enum('','Teaching','Fieldwork (including student dissertations)','Laboratory work','Health, safety or security purposes','Other') NOT NULL COMMENT 'This equipment will be used primarily for',
 			  `item1Description` varchar(255) NOT NULL COMMENT 'Item #1 description',
 			  `item1Amount` decimal(6,2) NOT NULL COMMENT 'Item #1 unit price (including VAT)',
 			  `item1Quantity` int NOT NULL COMMENT 'Item #1 quantity',
 			  `item2Description` varchar(255) DEFAULT NULL COMMENT 'Item #2 description',
 			  `item2Amount` decimal(6,2) DEFAULT NULL COMMENT 'Item #2 unit price (including VAT)',
 			  `item2Quantity` int DEFAULT NULL COMMENT 'Item #2 quantity',
 			  `item3Description` varchar(255) DEFAULT NULL COMMENT 'Item #3 description',
 			  `item3Amount` decimal(6,2) DEFAULT NULL COMMENT 'Item #3 unit price (including VAT)',
 			  `item3Quantity` int DEFAULT NULL COMMENT 'Item #3 quantity',
 			  `item4Description` varchar(255) DEFAULT NULL COMMENT 'Item #4 description',
 			  `item4Amount` decimal(6,2) DEFAULT NULL COMMENT 'Item #4 unit price (including VAT)',
 			  `item4Quantity` int DEFAULT NULL COMMENT 'Item #4 quantity',
 			  `item5Description` varchar(255) DEFAULT NULL COMMENT 'Item #5 description',
 			  `item5Amount` decimal(6,2) DEFAULT NULL COMMENT 'Item #5 unit price (including VAT)',
 			  `item5Quantity` int DEFAULT NULL COMMENT 'Item #5 quantity',
 			  `itemsAdditional` text COMMENT 'If you have more than 5 items and/or cannot simplify your request into 5 lines, please paste in the rows/columns here from your spreadsheet.',
 			  `comments` text COMMENT 'Are there any additional details you would like to include (e.g. website links, available discounts, lead times on particular items)?',
 			  `updatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Updated at',
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
		# Introduction
		$html  = "\n<p>The LFC administer funds for items between &pound;100-&pound;2,000 to support departmental lab and fieldwork.</p>";
		$html .= "\n<p>Completed applications are discussed at termly meetings.</p>";
		
		# Application button
		$html .= "\n<h3>Apply for a grant</h3>";
		$html .= "\n<br />";
		$html .= "\n<p><a href=\"{$this->baseUrl}/apply/\" class=\"actions\"><img src=\"/images/icons/add.png\" class=\"icon\" /> Apply for a grant</a></p>";
		
		# Show the HTML
		echo $html;
	}
	
	
	# Submission page for applicants
	public function apply ()
	{
		# Start the HTML
		$html = '';
		
		# Introduction
		$form = new form (array (
			'databaseConnection' => $this->databaseConnection,
			'div' => 'ultimateform horizontalonly',
			'unsavedDataProtection' => true,
			'displayRestrictions' => false,
			'nullText' => '',
			'submitButtonText' => 'Submit my application!',
			'formCompleteText' => $this->tick . ' Thank you. The labs team will reach out to let you know when your application has been considered.',
		));
		$form->dataBinding (array (
			'database' => $this->settings['database'],
			'table' => $this->settings['table'],
			'intelligence' => true,
			'attributes' => $this->submissionsDataBindingAttributes (true),
		));
		
		# Check amounts
		if ($unfinalisedData = $form->getUnfinalisedData ()) {
			if ($unfinalisedData['amount']) {
				$total = 0;
				$itemsFields = 5;	// Matching the database structure
				for ($i = 1; $i <= $itemsFields; $i++) {
					$total += ((float) $unfinalisedData["item{$i}Amount"] * (int) $unfinalisedData["item{$i}Quantity"]);
				}
				if ($total != $unfinalisedData['amount']) {
					$form->registerProblem ('totalMismatch', 'The total does not match the sum of the requested items. Please check the line items and the total.', 'amount');
				}
			}
		}
		
		# Set conformation e-mail to the user
		$form->setOutputConfirmationEmail ('email', $this->settings['administratorEmail'], 'Small equipment grant application', false, $displayUnsubmitted = false);
		
		# E-mail submissions to the team
		$form->setOutputEmail ($this->settings['recipientEmail'], $this->settings['administratorEmail'], 'Small equipment grant application from {email}', NULL, $replyToField = 'email', $displayUnsubmitted = false);
		
		# Insert the submission on form complete
		if ($submission = $form->process ($html)) {
			if (!$this->databaseConnection->insert ($this->settings['database'], $this->settings['table'], $submission)) {
				//application::dumpData ($this->databaseConnection->error ());
			}
		}
		
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
	private function submissionsDataBindingAttributes ($userForm = false)
	{
		# Set the databinding attributes
		$dataBindingAttributes = array (
			'email' => array ('editable' => (!$userForm), 'default' => $this->user . '@cam.ac.uk', ),
			'amount' => array ('prepend' => '&pound; ', ),
			'item1Description' => array ('heading' => array (3 => 'Details of your requested items'), ),
			'item1Amount' => array ('prepend' => '&pound; ', ),
			'item2Amount' => array ('prepend' => '&pound; ', ),
			'item3Amount' => array ('prepend' => '&pound; ', ),
			'item4Amount' => array ('prepend' => '&pound; ', ),
			'item5Amount' => array ('prepend' => '&pound; ', ),
			'itemsAdditional' => array ('rows' => 10, ),
		);
		
		# Return the attributes list
		return $dataBindingAttributes;
	}
}

?>
