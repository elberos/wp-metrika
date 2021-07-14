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


if ( !class_exists( Leads_Table::class ) ) 
{

class Leads_Table extends \Elberos\Table
{
	
	/**
	 * Table name
	 */
	function get_table_name()
	{
		global $wpdb;
		return $wpdb->base_prefix . 'elberos_facebook_leads';
	}
	
	
	
	/**
	 * Page name
	 */
	function get_page_name()
	{
		return "elberos-facebook-leads";
	}
	
	
	
	/**
	 * Create struct
	 */
	static function createStruct()
	{
		$struct = \Elberos\Facebook\Leads\Leads::create
		(
			"admin_table",
			function ($struct)
			{
				$struct->addField
				([
					"api_name" => "data",
					"label" => "Данные",
					"virtual" => true,
					"form_render" => function($struct, $field, $item)
					{
						$form_data = @json_decode($item["data"], true);
						foreach ($form_data as $row)
						{							
							echo esc_html($row["name"]) . ": " . esc_html( implode(", ", $row["values"]) ) . "<br/>";
						}
					}
				]);
				
				$struct->table_fields =
				[
					"facebook_id",
					"gmtime_lead_create",
				];
				
				$struct->form_fields =
				[
					"facebook_id",
					"data",
					"gmtime_lead_create",
				];
				
				$struct->editField("facebook_id", [ "readonly" => true ]);
				$struct->editField("gmtime_lead_create", [ "readonly" => true ]);
				
				return $struct;
			}
		);
		
		return $struct;
	}
	
	
	
	/**
	 * Init struct
	 */
	function initStruct()
	{
		parent::initStruct();
	}
	
	
	
	/* Заполнение колонки cb */
	function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }
	
	
	
	/**
	 * Column buttons
	 */
	function column_buttons($item)
	{
		$page_name = $this->get_page_name();
		$id = isset($_GET['id']) ? $_GET['id'] : '';
		return sprintf
		(
			'<a href="?page=' . $page_name . '&action=edit&id=%s">%s</a>',
			$item['id'], __('Редактировать', 'elberos-facebook-leads')
		);
	}
	
	
	
	/**
	 * Действия
	 */
	function get_bulk_actions()
	{
		$is_deleted = isset($_REQUEST['is_deleted']) ? $_REQUEST['is_deleted'] : "";
		if ($is_deleted != 'true')
		{
			$actions = array
			(
				'trash' => 'Переместить в корзину',
			);
		}
		else
		{
			$actions = array
			(
				'notrash' => 'Восстановить из корзины',
				'delete' => 'Удалить навсегда',
			);
		}
		return $actions;
	}
	
	
	
	/**
	 * Process bulk action
	 */
	function process_bulk_action()
	{
		$action = $this->current_action();
		
		/* Edit items */
		if (in_array($action, ['add', 'edit']))
		{
			parent::process_bulk_action();
		}
		
		/* Move to trash items */
		else if (in_array($action, ['trash', 'notrash', 'delete']))
		{
			parent::process_bulk_action();
		}
	}
	
	
	
	/**
	 * Get item
	 */
	function do_get_item()
	{
		parent::do_get_item();
	}
	
	
	
	/**
	 * Process item
	 */
	function process_item($item, $old_item)
	{
		return $item;
	}
	
	
	
	/**
	 * Item validate
	 */
	function item_validate($item)
	{
		return "";
	}
	
	
	
	/**
	 * Prepare table items
	 */
	function prepare_table_items()
	{
		$args = [];
		$where = [];
		
		/* Is deleted */
		if (isset($_GET["is_deleted"]) && $_GET["is_deleted"] == "true")
		{
			$where[] = "is_deleted=1";
		}
		else
		{
			$where[] = "is_deleted=0";
		}
		
		$per_page = $this->per_page();
		list($items, $total_items, $pages, $page) = \Elberos\wpdb_query
		([
			"table_name" => $this->get_table_name(),
			"where" => implode(" and ", $where),
			"args" => $args,
			"page" => (int) isset($_GET["paged"]) ? ($_GET["paged"] - 1) : 0,
			"per_page" => $per_page,
		]);
		
		$this->items = $items;
		$this->set_pagination_args(array(
			'total_items' => $total_items, 
			'per_page' => $per_page,
			'total_pages' => ceil($total_items / $per_page) 
		));
	}
	
	
	
	/**
	 * CSS
	 */
	function display_css()
	{
		parent::display_css();
	}
	
	
	
	/**
	 * Display table sub
	 */
	function display_table_sub()
	{
		$page_name = $this->get_page_name();
		$is_deleted = isset($_GET['is_deleted']) ? $_GET['is_deleted'] : "";
		$url = "admin.php?page=" . $page_name;
		?>
		<ul class="subsubsub">
			<li>
				<a href="<?= esc_attr($url) ?>"
					class="<?= ($is_deleted != "true" ? "current" : "")?>"  >Все</a> |
			</li>
			<li>
				<a href="<?= esc_attr($url) . "&is_deleted=true" ?>"
					class="<?= ($is_deleted == "true" ? "current" : "")?>" >Корзина</a>
			</li>
		</ul>
		<?php
	}
	
	
	
	/**
	 * Display form sub
	 */
	function display_form_sub()
	{
		parent::display_form_sub();
	}
	
	
	
	/**
	 * Returns form title
	 */
	function get_form_title($item)
	{
		return _e($item['id'] > 0 ? 'Редактировать лида' : 'Добавить лид', 'elberos-facebook-leads');
	}
	
	
	
	/**
	 * Returns table title
	 */
	function get_table_title()
	{
		return "Лиды";
	}
	
	
	
	/**
	 * Display table add button
	 */
	function display_table_add_button()
	{
		$page_name = $this->get_page_name();
		$id = isset($_GET['id']) ? $_GET['id'] : '';
		?>
		<a href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=' . $page_name . '&action=catalog&id=' . $id . '&sub=add');?>"
			class="page-title-action"
		>
			<?php _e('Add new', 'elberos-core')?>
		</a>
		<?php
	}
	
	
	
	/**
	 * Display action
	 */
	function display_action()
	{
		$action = $this->current_action();
		parent::display_action();
	}
	
	
}

}