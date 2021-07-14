<?php

/*!
 *  Elberos Facebook Lead Form Connector
 *
 *  (c) Copyright 2021 "Ildar Bikmamatov" <support@elberos.org>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      https://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace Elberos\Facebook\Leads;

if ( !class_exists( Forms::class ) ) 
{

class Forms extends \Elberos\StructBuilder
{
	
	/**
	 * Get entity name
	 */
	public static function getEntityName()
	{
		return "elberos_facebook_leads_forms";
	}
	
	
	
	/**
	 * Init struct
	 */
	public function init()
	{
		$this
			->addField
			([
				"api_name" => "facebook_id",
				"label" => "ID формы",
				"type" => "input",
			])
			
			->addField
			([
				"api_name" => "name",
				"label" => "Название формы",
				"type" => "input",
			])
			
			->addField
			([
				"api_name" => "status",
				"label" => "Статус",
				"type" => "input",
			])
		;
	}
	
	
}

}