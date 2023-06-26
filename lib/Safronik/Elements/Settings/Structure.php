<?php

namespace Safronik\Layout\Settings;

class Structure{
	
	public static function getStructure(){
		return [
			'page_control' => [
				'type' => 'section',
				'title' => 'Page Control',
				'children' => [
					'Delete Page' => [
						'type' => 'dropdown_list',
						'title' => __('Overview', 'Safronik'),
						'parent' => 'asec_main',
						'active' => true,
						'children' => [
							'debug_enable' => [
								'type' => 'section',
								'title' => __('Common information', 'Safronik'),
							],
						],
					],
					'dashboard_activity' => [
						'type' => 'tab',
						'title' => __('Dashboard Activity', 'Safronik'),
						'parent' => 'asec_main',
						'active' => false,
						'children' => [
							'visitors_table' => [
								'type' => 'section',
								'title' => __('Common information', 'Safronik'),
								'callback' => 'Safronik\Modules\BruteForceProtection\TableView::drawMonitoringTable',
							],
						]
					],
					'visitors' => [
						'type' => 'tab',
						'title' => __('Visitors', 'Safronik'),
						'parent' => 'asec_main',
						'active' => false,
						'children' => [
							'visitors_table' => [
								'type' => 'section',
								'title' => __('Common information', 'Safronik'),
								//'callback' => '\Safronik\Layout\Table\Table::drawVisitorsTable',
							],
						]
					],
					'asec_dashboard' => [
						'type' => 'tab',
						'title' => __('Settings', 'Safronik'),
						'parent' => 'asec_main',
						'children' => [
							'firewall_settings' => [
								'type' => 'section',
								'title' => __('Firewall', 'Safronik'),
								'parent' => 'asec_dashboard',
								'children' => [
									'firewall' => [
										'type' => 'checkbox',
										'title' => __('Enable Firewall', 'Safronik'),
										'icon' => 'firewall',
										'children' => [
											'web_application_firewall' => [
												'type' => 'checkbox',
												'title' => __('Web Application Firewall', 'Safronik'),
												'icon' => 'padlock',
											],
											'traffic_control' => [
												'type' => 'checkbox',
												'title' => __('Traffic Control', 'Safronik'),
												'icon' => 'padlock',
											]
										],
									],
								],
							],
							'monitoring_settings' => [
								'type' => 'section',
								'title' => __('Monitoring', 'Safronik'),
								'parent' => 'asec_dashboard',
								'children' => [
									'monitoring' => [
										'type' => 'checkbox',
										'title' => __('Enable Monitoring', 'Safronik'),
										'icon' => 'padlock',
										'description' => __('Start to monitoring actions like authorization and dashboard activity', 'Safronik'),
										'children' => [
											'brute_force_protection' => [
												'type' => 'checkbox',
												'title' => __('Enable Brute Force Protection', 'Safronik'),
												'icon' => 'padlock',
											],
											'monitoring_dashboard' => [
												'type' => 'checkbox',
												'title' => __('Monitoring Dashboard Activity', 'Safronik'),
												'icon' => 'padlock',
											],
										],
									],
								],
							],
						],
					],
				],
			],
		];
	}
}