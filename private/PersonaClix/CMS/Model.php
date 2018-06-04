<?php

namespace PersonaClix\CMS;

class Model {

	// Database instance
	public static $database;

	// Database table name
	protected static $table;

	/**
	 *	Fetch all records from the database and return them as
	 *	an array of Model instances with properties.
	 *	@return array An array of Model instances.
	 */
	public static function all() : array {
		// Get the class name being called.
		$class_name = get_called_class();

		// Fetch all records.
		$output = $class_name::match([]);

		// Return the output array.
		return $output;
	}

	/**
	 *	Fetch all records from the database that match the array of properties,
	 *	and return them as an array of Model instances with properties.
	 *	@param array Associative Array of Key => Value pairs to act as a WHERE clause.
	 *	@return array An array of Model instances.
	 */
	public static function match(array $properties) : array {
		// Get the class name being called.
		$class_name = get_called_class();

		// Check if the database variable has been set and that it is a 
		// valid instance of the Engine's Database class,
		// otherwise just return an empty array since we can't do anything without it.
		if(!$class_name::$database || !$class_name::$database instanceof \PersonaClix\Engine\Database)
			return [];

		// Check for custom table name and generate one if not set.
		// The generated table name will be a lowercase version of the class name with an added "s" on the end.
		// e.g. A model called "User" will generate a table name of "users".
		if(!$class_name::$table)
			$class_name::$table = strtolower($class_name) . "s";

		// Select everything from the database table and store it in a variable of the same name.
		$results = $class_name::$database->select(['*'], $class_name::$table, $properties);

		// Create an array to hold the output.
		$output = [];

		// Check if at least one record was returned.
		if(count($results) > 0) {
			// Loop through each record.
			foreach ($results as $result) {
				// Create instance of class.
				$$class_name = new $class_name;
				
				// Loop through each record property.
				foreach ($result as $property => $value) {
					// Set the class property with the same name to the record value.
					$$class_name->$property = $value;
				}

				// Add class instance to output array.
				$output[] = $$class_name;
			}
		}

		// Return the output array.
		return $output;
	}

	/**
	 *	Update the database with the Model property values.
	 */
	public function update() {
		if(!isset($this->id))
			return;

		$class_name = get_called_class();

		// Check for custom table name and generate one if not set.
		// The generated table name will be a lowercase version of the class name with an added "s" on the end.
		// e.g. A model called "User" will generate a table name of "users".
		if(!$class_name::$table)
			$class_name::$table = strtolower($class_name) . "s";
		
		// Fetch the record from the database that matches the current instance based on the ID property.
		$result = $class_name::match(['id' => $this->id]);

		// Only one result should be returned above, but just in case, we only want the first result.
		$result = count($result) > 0 ? $result[0] : [];

		// Empty array to hold properties to update.
		$update = [];

		// Loop through all properties in the database.
		foreach ($result as $property => $value) {
			// Ignore the id property.
			if($property == "id")
				continue;

			// Add the new value of the property to an update array.
			$update[$property] = $this->$property;
		}

		// Does the update array have at least one property to update?
		if(count($update) > 0) {
			// Update the database with the new properties where the ID matches.
			$class_name::$database->update($class_name::$table, $update, ['id' => $this->id]);
		}
	}

}
