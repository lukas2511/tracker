<?php
	
	requires(
		'/Model/Project',
		
		'/Model/Ticket',
		'/Model/EncodingProfile',
		'/Model/EncodingProfileVersion'
	);
	
	class Controller_API extends Controller {
		
		protected $beforeAction = ['setProject' => true];
		
		protected function setProject($action, array $arguments) {
			if (!isset($arguments['project_slug'])) {
				return;
			}
			
			$this->project = Project::findBy(['slug' => $arguments['project_slug']]);
		}
		
		public function tickets_fahrplan() {
			$tickets = Ticket::findAll()
				->select('id, fahrplan_id, title')
				->where([
					'project_id' => $this->project['id'],
					'ticket_type' => 'meta'
				])
				->scoped([
					'with_default_properties',
					'with_encoding_profile_name',
					'order_list'
				]);
			
			return $this->_respond($tickets);
		}
		
		public function tickets_released() {
			$tickets = Ticket::findAll()
				->select(
					'fahrplan_id'
				)
				->where([
					'project_id' => $this->project['id'],
					'ticket_type' => 'encoding',
					'ticket_state' => 'released'
				])
				->scoped([
					'with_recording',
					'with_merged_properties' => [[
						'Fahrplan.GUID' => 'fahrplan_guid',
						'Record.Cutdiffseconds' => 'duration',
						'YouTube.Url0' => 'youtube_url',
						'YouTube.Url1' => 'youtube_url_translated',
						'YouTube.Url2' => 'youtube_url_translated_2'
					]]
				]);
			
			return $this->_respond($tickets);
		}
		
		protected function _respond($data) {
			if ($data instanceOf Model_Resource) {
				$data = $data->toArray();
			}
			
			if ($this->respondTo('json')) {
				$this->Response->setContent(json_encode($data));
				return $this->Response;
			}
			
			return Response::error(400);
		}
		
	}
	
?>