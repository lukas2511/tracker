<?php
	
	requires(
		'Random',
		
		'/Model/EncodingProfileVersion',
		'/Model/EncodingProfile',
		'/Model/Handle',
		'/Model/Ticket',
		
		'/Model/Worker',
		'/Model/WorkerGroup'
	);
	
	class Controller_Workers extends Controller_Application {
		
		public $requireAuthorization = true;
		
		public function index() {
			$this->groups = WorkerGroup::findAll()
				->includes(['Worker']);
			
			return $this->render('workers/index');
		}
		
		public function queue(array $arguments) {
			$this->group = WorkerGroup::findOrThrow($arguments['id']);
			
            $tickets = Ticket::findAll()
                ->from('view_serviceable_tickets', 'tbl_ticket')
                ->where([
					'project_id' => $this->group->Project->pluck('id'),
					'next_state_service_executable' => true
				])
                ->orderBy('priority DESC')
				->pluck('id');
			
			if (!empty($tickets)) {
	            $this->queue = Ticket::findAll()
					// Join Handle for parent tickets, children should not be assigned
					->andSelect('ticket_priority(id) AS priority_product')
					->join([
						'Handle',
						'Project'
					])
					->scoped([
						'with_child',
						'with_default_properties',
						'with_encoding_profile_name',
						'with_progress',
						'order_priority'
					])
	                ->orWhere([
						'id' => $tickets,
						'child.id' => $tickets
					]);
			}
			
			return $this->render('workers/group/queue');
		}
		
		public function create_group() {
			$this->form();
			
			$group = new WorkerGroup($this->form->getValues());
			$group['token'] = Random::friendly(32);
			$group['secret'] = Random::friendly(32);
			
			if ($this->form->wasSubmitted() and $group->save()) {
				$this->flash('Worker group created');
				return $this->redirect('workers', 'index');
			}
			
			return $this->render('workers/group/edit');
		}
		
		public function edit_group(array $arguments) {
			$this->group = WorkerGroup::findOrThrow($arguments['id']);
			
			$this->form();
			
			if ($this->form->wasSubmitted()) {
				if ($this->form->getValue('create_secret')) {
					$this->group['secret'] = Random::friendly(32);
				}
				
				if ($this->group->save($this->form->getValues())) {
					if ($this->form->getValue('create_secret')) {
						$this->flashNow('New secret created');
					} else {
						$this->flash('Worker group updated');
						return $this->redirect('workers', 'index');
					}
				}
			}
			
			return $this->render('workers/group/edit');
		}
		
		public function delete_group(array $arguments) {
			WorkerGroup::deleteOrThrow($arguments['id']);
			
			$this->flash('Worker group deleted');
			return $this->redirect('workers', 'index');
		}
		
	}
	
?>