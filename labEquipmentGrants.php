<?php

# Class to create a template application
class labEquipmentGrants extends frontControllerApplication
{
	# Class properties
	private $userIsCommitteeMember = false;
	
	# Function to assign defaults additional to the general application defaults
	public function defaults ()
	{
		# Specify available arguments as defaults or as NULL (to represent a required argument)
		$defaults = array (
			'applicationName'				=> 'Small equipment grant applications',
			'div'							=> strtolower (__CLASS__),
			'tabUlClass'					=> 'tabsflat',
			'databaseStrictWhere'			=> true,
			'nativeTypes'					=> true,
			'administrators'				=> 'administrators',
			'database'						=> 'labequipmentgrants',
			'table'							=> 'submissions',
			'settingsTableExplodeTextarea'	=> array ('committeeMembers'),
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
			'undecided' => array (
				'description' => 'Undecided',
				'url' => 'undecided/',
				'tab' => 'Undecided submissions',
				'icon' => 'application_cascade',
				'authentication' => true,
				'enableIf' => ($this->userIsCommitteeMember || $this->userIsAdministrator),
			),
			'submissions' => array (
				'description' => 'Edit submissions',
				'url' => 'submissions/',
				'tab' => 'Edit submissions',
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
			  `maximumAmount` int NOT NULL COMMENT 'Maximum amount (£)',
			  `committeeMembers` text NOT NULL COMMENT 'Committee member usernames (one per line)',
			  `introductionHtml` TEXT NULL DEFAULT NULL COMMENT 'Introduction text'
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Settings';
			INSERT INTO settings (id) VALUES (1);
			
			-- My table
			CREATE TABLE IF NOT EXISTS `submissions` (
 			  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Automatic key',
 			  `title` varchar(255) NOT NULL COMMENT 'Title',
			  `name` varchar(255) NOT NULL COMMENT 'Name',
 			  `email` varchar(255) NOT NULL COMMENT 'E-mail',
			  `supervisorEmail` VARCHAR(255) NULL COMMENT 'Supervisor e-mail (for PhD students only)',
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
			  `confirmation` TINYINT NOT NULL COMMENT 'I confirm the details provided here are correct, and estimated costs include all associated fees (e.g. shipping, VAT, etc.) at the date of submission.',
 			  `status` enum('Submitted','Approved','Rejected') NOT NULL DEFAULT 'Submitted' COMMENT 'Status',
 			  `internalNotes` text COMMENT 'Internal notes',
 			  `updatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Updated at',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='My table';
		";
	}
	
	
	
	# Additional processing, pre-actions phase
	public function mainPreActions ()
	{
		# Determine if the user is a committee member
		$this->userIsCommitteeMember = ($this->user && in_array ($this->user, $this->settings['committeeMembers']));
	}
	
	
	# Additional processing
	public function main ()
	{
		
	}
	
	
	
	# Home page
	public function home ()
	{
		# Introduction
		$html = $this->settings['introductionHtml'];
		
		# Application button
		$html .= "\n<h3>Apply for a grant</h3>";
		$html .= "\n<br />";
		$html .= "\n<p><a href=\"{$this->baseUrl}/apply/\" class=\"actions\"><img src=\"/images/icons/add.png\" class=\"icon\" /> Apply for a grant</a></p>";
		
		# Submissions (for admins)
		if ($this->userIsAdministrator) {
			$totalUndecided = $this->getUndecidedTotal ();
			$html .= "\n<h3>Undecided submissions</h3>";
			$html .= "\n<br />";
			$html .= "\n<p><a href=\"{$this->baseUrl}/undecided/\" class=\"actions\"><img src=\"/images/icons/application_cascade.png\" class=\"icon\" /> View undecided submissions ({$totalUndecided})</a></p>";
		}
		
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
			'div' => 'ultimateform horizontalonly applicationform',
			'unsavedDataProtection' => true,
			'displayRestrictions' => false,
			'nullText' => '',
			'display' => 'template',
			'displayTemplate' => $this->formTemplate (),
			'submitButtonText' => 'Submit my application!',
			'formCompleteText' => $this->tick . ' Thank you. The labs team will reach out to let you know when your application has been considered.',
			'cols' => 60,
			'autofocus' => true,
		));
		$form->dataBinding (array (
			'database' => $this->settings['database'],
			'table' => $this->settings['table'],
			'intelligence' => true,
			'exclude' => array ('status', 'internalNotes'),
			'attributes' => $this->submissionsDataBindingAttributes (true),
			'int1ToCheckbox' => true,
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
		$form->setOutputConfirmationEmail ('email', $this->settings['administratorEmail'], 'Small equipment grant application: {title}', false, $displayUnsubmitted = false);
		
		# E-mail submissions to the team
		$form->setOutputEmail ($this->settings['recipientEmail'], $this->settings['administratorEmail'], 'Small equipment grant application from {name}: {title}', NULL, $replyToField = 'email', $displayUnsubmitted = false);
		
		# Insert the submission on form complete
		if ($submission = $form->process ($html)) {
			if (!$this->databaseConnection->insert ($this->settings['database'], $this->settings['table'], $submission)) {
				//application::dumpData ($this->databaseConnection->error ());
			}
		}
		
		# Show the HTML
		echo $html;
	}
	
	
	# Undecided submissions
	public function undecided ()
	{
		# Start the HTML
		$undecidedSubmissions = $this->getUndecided ();
		$totalUndecided = count ($undecidedSubmissions);
		$html = "\n<p>" . ($totalUndecided ? ($totalUndecided == 1 ? "There is currently 1 undecided submission:" : "There are currently {$totalUndecided} undecided submissions, listed in order of submission:") : 'There are no undecided submissions.') . '</p>';
		
		# Show each submission, earliest first
		foreach ($undecidedSubmissions as $id => $submission) {
			$html .= "\n<h3 class=\"undecided\">#{$id}: " . htmlspecialchars ($submission['title']) . '</h3>';
			$html .= $this->templatiseRecord ($submission);
		}
		
		# Show the HTML
		echo $html;
	}
	
	
	# Function to templatise a submission for display
	private function templatiseRecord ($submission)
	{
		# Get the template
		$templateHtml = $this->formTemplate (false);
		$templateHtml = '<div class="ultimateform horizontalonly applicationform">' . $templateHtml . '</div>';
		$templateHtml = "\n<div class=\"graybox\">" . $templateHtml . "\n</div>";
		
		# Add supplementary data
		$submission['_heading1'] = '<h3>Details of your requested items</h3>';
		
		# Prepare data
		$placeholders = array ();
		foreach ($submission as $field => $value) {
			$placeholders['{' . $field . '}'] = (strlen ($value) ? $value : '-');
		}
		
		# Substitute values
		$html = strtr ($templateHtml, $placeholders);
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to get the total of undecided submissions
	private function getUndecided ()
	{
		# Get and return the total
		return $this->databaseConnection->select ($this->settings['database'], $this->settings['table'], array ('status' => 'Submitted'), array (), true, $orderBy = 'updatedAt ASC');
	}
	
	
	# Function to get total undecided submissions
	private function getUndecidedTotal ()
	{
		# Get and return the total
		return $this->databaseConnection->getTotal ($this->settings['database'], $this->settings['table'], "WHERE status = 'Submitted'");
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
			'cols' => 60,
			'display' => 'template',
			'displayTemplate' => $this->formTemplate (true, $includeInternalFields = true),
			'hideAddRecord' => true,
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
			'name' => array ('editable' => (!$userForm), 'default' => $this->userName, ),
			'amount' => array ('prepend' => '&pound; ', 'max' => $this->settings['maximumAmount'], ),
			'item1Description' => array ('heading' => array (3 => 'Details of your requested items'), ),
			'item1Amount' => array ('prepend' => '&pound; ', ),
			'item2Amount' => array ('prepend' => '&pound; ', ),
			'item3Amount' => array ('prepend' => '&pound; ', ),
			'item4Amount' => array ('prepend' => '&pound; ', ),
			'item5Amount' => array ('prepend' => '&pound; ', ),
			'itemsAdditional' => array ('rows' => 10, ),
			'status' => array ('heading' => array (3 => 'Decision (internal information)', 'p' => '(This will not e-mail the applicant.)'), ),
		);
		
		# Return the attributes list
		return $dataBindingAttributes;
	}
	
	
	# Form template
	public function formTemplate ($formMode = true, $includeInternalFields = false)
	{
		# Create the main table rows as HTML
		$html  = '
				<tr>
					<td class="title">Title:&nbsp;*</td>
					<td class="data">{title}</td>
				</tr>
				<tr>
					<td class="title">Name:<span class="requirednoneditable">&nbsp;*</span></td>
					<td class="data">{name}</td>
				</tr>
				<tr>
					<td class="title">E-mail:<span class="requirednoneditable">&nbsp;*</span></td>
					<td class="data">{email}</td>
				</tr>
				<tr>
					<td class="title">Supervisor e-mail (for PhD students only):<span class="requirednoneditable">&nbsp;*</span></td>
					<td class="data">{supervisorEmail}</td>
				</tr>
				<tr>
					<td class="title">Amount requested (including VAT):&nbsp;*</td>
					<td class="data">{amount}</td>
				</tr>
				<tr>
					<td class="title">Short justification of equipment requested:&nbsp;*</td>
					<td class="data">{description}</td>
				</tr>
				<tr>
					<td class="title">This equipment will be used primarily for:&nbsp;*</td>
					<td class="data">
						{purpose}
					</td>
				</tr>
				<tr>
					<td colspan="2">{_heading1}</td>
				</tr>
				<tr>
					<td colspan="2">
						<table>
							<tr>
								<th></th>
								<th>Description</th>
								<th>Amount</th>
								<th>Quantity</th>
							</tr>
							<tr>
								<td>1. *</td>
								<td>{item1Description}</td>
								<td class="amount">{item1Amount}</td>
								<td>{item1Quantity}</td>
							</tr>
							<tr>
								<td>2.</td>
								<td>{item2Description}</td>
								<td class="amount">{item2Amount}</td>
								<td>{item2Quantity}</td>
							</tr>
							<tr>
								<td>3.</td>
								<td>{item3Description}</td>
								<td class="amount">{item3Amount}</td>
								<td>{item3Quantity}</td>
							</tr>
							<tr>
								<td>4.</td>
								<td>{item4Description}</td>
								<td class="amount">{item4Amount}</td>
								<td>{item4Quantity}</td>
							</tr>
							<tr>
								<td>5.</td>
								<td>{item5Description}</td>
								<td class="amount">{item5Amount}</td>
								<td>{item5Quantity}</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td class="title">If you have more than 5 items and/or cannot simplify your request into 5 lines, please paste in the rows/columns here from your spreadsheet.:</td>
					<td class="data">{itemsAdditional}</td>
				</tr>
				<tr>
					<td class="title">Are there any additional details you would like to include (e.g. website links, available discounts, lead times on particular items)?:</td>
					<td class="data">{comments}</td>
				</tr>
				<tr>
					<td class="title">I confirm the details provided here are correct, and estimated costs include all associated fees (e.g. shipping, VAT, etc.) at the date of submission:</td>
					<td class="data">{confirmation}</td>
				</tr>
		';
		
		# For sinenomine editing, include internal fields
		if ($includeInternalFields) {
			$html .= '
				<tr>
					<td colspan="2">{_heading2}</td>
				</tr>
				<tr>
					<td colspan="2">{_heading3}</td>
				</tr>
				<tr>
					<td class="title">ID:&nbsp;*</td>
					<td class="data">{id}</td>
				</tr>
				<tr>
					<td class="title">Status:&nbsp;*</td>
					<td class="data">{status}</td>
				</tr>
				<tr>
					<td class="title">Internal notes:&nbsp;*</td>
					<td class="data">{internalNotes}</td>
				</tr>
			';
		}
		
		# Complete the table
		$html = "\n" . '<table summary="Online submission form">' . $html . "\n</table>";
		
		# In form mode, add problems/submit areas
		if ($formMode) {
			$html = "\n{[[PROBLEMS]]}\n" . $html . "\n{[[SUBMIT]]}\n";
		}
		
		# Return the HTML
		return $html;
	}
}

?>
