<?php
class ControllerCommonColumnLeft extends Controller {
	public function index() {
		if (isset($this->request->get['token']) && isset($this->session->data['token']) && ($this->request->get['token'] == $this->session->data['token'])) {
			$data['profile'] = $this->load->controller('common/profile');
			$data['menu'] = $this->load->controller('common/menu');
			$data['stats'] = $this->load->controller('common/stats');

			$user_group = $this->model_user_user_group -> getUserGroupByUserId($this->user->getId(), "g.name");
			if (in_array($user_group['name'], ['hangzhou', 'shandong'])) {
			    $data['is_sd_or_zj'] = true;
			}else {
			    $data['is_sd_or_zj'] = false;
			}
			return $this->load->view('common/column_left.tpl', $data);
		}
	}
}