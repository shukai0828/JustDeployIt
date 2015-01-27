<?php
App::uses('AppController', 'Controller');

/**
 * Servers Controller
 *
 */
class ServersController extends AppController
{

    public function beforeFilter() {
        parent::beforeFilter();

        $this->loadModel('Project');
        $project = $this->Project->findById($this->request->pass[0]);

        $this->set($project);
        $this->set('needProjectMenuBar', true);
    }

    public function index($project_id) {
        // TODO:用户权限判断
       $serverList = $this->Server->findAllByProjectId($project_id);

        $this->set('ServerList', $serverList);
    }

    public function add($project_id)
    {
        // TODO:需要进行服务器可访问检查
        if ($this->request->is('post')) {
            $server = $this->Server->create($this->request->data['server']);
            $server['Server']['user_id'] = $this->Auth->user('id');
            if($this->Server->save($server)) {
                $this->Session->setFlash('Server has been added successfully!', 'common/flash', array('type' => 'success'), 'function');
                $this->redirect(array('action' => 'index', $project_id));
            } else {
                $this->Session->setFlash('Something went wrong when creating server!', 'common/flash', array('type' => 'alert'), 'function');
                $this->set($server);
            }
        } elseif (isset($this->request->query['type'])) {
            // 处理用户选择不同服务器类型时的页面渲染内容
            // type的值为ssh或ftp
            return $this->render($this->request->query['type'], ''); // 函数执行结束，返回不带layout的页面内容
        }

        // 正常渲染空值的add页面
        $this->_setBaseInfo($project_id);
    }

    public function edit($project_id, $id) {

        // 请求修改
        if($this->request->is('post')) {
            $server = $this->Server->create($this->request->data['server']);
            $server['Server']['user_id'] = $this->Auth->user('id');
            if($this->Server->save($server)) {
                $this->Session->setFlash('Server has been modified successfully!', 'common/flash', array('type' => 'success'), 'function');
                $this->redirect(array('action' => 'index', $project_id));
            } else {
                $this->Session->setFlash('Something went wrong when saving server!', 'common/flash', array('type' => 'alert'), 'function');
                $this->set($server);
            }
        }  else {

            $server = $this->Server->findById($id);

            $this->set($server);
        }
    }

    public function del($project_id, $id) {
        // TODO: 1. 还会有其他的关联数据; 2. 要进行权限判断；
        if($this->Server->delete($id)) {
            $this->Session->setFlash('Server has been removed successfully!', 'common/flash', array('type' => 'success'), 'function');
        } else {
            $this->Session->setFlash('Something went wrong when deleting server!', 'common/flash', array('type' => 'alert'), 'function');
        }

        $this->redirect(array('action' => 'index', $project_id));

    }

    protected function _setBaseInfo($project_id) {
        // 获取可用的ServerGroup
        $this->loadModel('ServerGroup');
        $serverGroup = $this->ServerGroup->findByProjectId($project_id);
        // 获取可用的分支
        $this->loadModel('Repository');
        $repository = $this->Repository->findByProjectId($project_id);
        $repoPath = $this->Repository->initGitrepo($project_id);
        $repository['Repository']['branches'] = $this->Repository->branches($repoPath);

        $this->set($serverGroup);
        $this->set($repository);
    }
}