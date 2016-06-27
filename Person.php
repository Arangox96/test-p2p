<?php
	class Person{
		/**
		 * @return object
		 */
		public function createPerson(
			$documentType, $document, $firstName, $lastName, $company = null,
			$emailAddress, $address = null, $city = null, $province = null, $country = null,
			$phone = null, $mobile = null)
		{
			if (!in_array($documentType, array('CC', 'CE', 'NIT', 'TI', 'PPN', 'TAX', 'RC')))
				throw new Exception(__CLASS__ . '::' . __METHOD__ . ': Se espera un tipo de documento entre [CC, CE, TI, NIT, PPN, TAX, RC]', 1011);
			if (!empty($emailAddress) && !filter_var($emailAddress, FILTER_VALIDATE_EMAIL))
				throw new Exception(__CLASS__ . '::' . __METHOD__ . ': Se espera una dirección de correo válida', 1012);	
			if (!empty($country) && (strlen($country) != 2))
				throw new Exception(__CLASS__ . '::' . __METHOD__ . ': Se espera que el país sea un ISO', 1013);

			$person = new stdClass();
			$person->documentType = $documentType;
			$person->document = $document;
			$person->firstName = $firstName;
			$person->lastName = $lastName;
			$person->company = $company;
			$person->emailAddress = $emailAddress;
			$person->address = $address;
			$person->city = $city;
			$person->province = $province;
			$person->country = $country;
			$person->phone = $phone;
			$person->mobile = $mobile;
			return $person;
		}
}