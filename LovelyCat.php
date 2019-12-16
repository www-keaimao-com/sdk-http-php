<?php


class lovelyCat
{
    private static $instance;
    /**
     * @var int 事件类型<br><br> 100=> 私聊消息<br>200=> 群聊消息<br>300=> 暂无<br>400=> 群成员增加<br>410=> 群成员减少<br>500=> 收到好友请求<br>600=> 二维码收款<br>700=> 收到转账<br>800=> 软件开始启动<br>900=> 新的账号登录完成<br>910=> 账号下线
     */
    public $type;
    /**
     * @var string 1级来源id（群消息事件下，1级来源为群id，2级来源为发消息的成员id，私聊事件下都一样）
     */
    public $from_wxid;
    /**
     * @var string 1级来源昵称（比如发消息的人昵称）<br>
     */
    public $from_name;
    /**
     * @var string 2级来源id（群消息事件下，1级来源为群id，2级来源为发消息的成员id，私聊事件下都一样）
     */
    public $final_from_wxid;
    /**
     * @var string 2级来源昵称
     */
    public $final_from_name;
    /**
     * @var string 当前登录的账号（机器人）标识id
     */
    public $robot_wxid;
    /**
     * @var string 消息内容
     */
    public $msg;
    /**
     * @var int 消息类型（请务必使用新版http插件）<br><br> 1 =>文本消息 <br>3 => 图片消息 <br>34 => 语音消息 <br>42 => 名片消息 <br>43 =>视频 <br>47 => 动态表情 <br> 48 =>地理位置<br>49 => 分享链接 <br>2001 => 红包<br>2002 => 小程序<br>2003 => 群邀请 <br><br>更多请参考sdk模块常量值
     * ）
     */
    public $msg_type;
    /**
     * @var string 如果是文件消息（图片、语音、视频、动态表情），这里则是可直接访问的网络地址，非文件消息时为空
     */
    public $file_url;
    /**
     * @var int 请求时间(时间戳10位版本)
     */
    public $time;
    /**
     * @var string 机器猫接口地址
     */
    public $url = "http://127.0.0.1:8073/send";

    public function __construct($config = null)
    {
        if (!empty($config)) {//未配置，使用默认配置
            $this->url = $config['url'];
        }
        $this->parseWechat($_POST);
    }

    /**
     * 单例模式使用
     * @param string|null $url 可爱猫API接口地址
     * @return lovelyCat
     */
    public static function getInstance($url = null)
    {
        if (!isset(self::$instance)) {
            $instance = new self(['url' => $url]);
            self::$instance = $instance;
        }
        return self::$instance;
    }

    /**
     * 解析可爱猫回调消息
     * @param $data
     */
    public function parseWechat($data)
    {
        $this->type = $data['type'];
        $this->from_wxid = $data['from_wxid'];
        $this->from_name = urldecode($data['from_name']);
        $this->final_from_wxid = $data['final_from_wxid'];
        $this->final_from_name = urldecode($data['final_from_name']);
        $this->robot_wxid = $data['robot_wxid'];
        $this->msg = urldecode($data['msg']);
        $this->msg_type = intval($data['msg']);
        $this->file_url = $data['file_url'];
        $this->msg = urldecode($data['msg']);
        $this->time = $data['time'];
    }


    /**
     * 发送文字消息(好友或者群)
     *
     * @access public
     * @param string $msg 消息内容
     * @param string $robwxid 登录账号id，用哪个账号去发送这条消息,不传则使用当前回调的机器人ID
     * @param string $to_wxid 对方的id，可以是群或者好友id，不传则表示回复当前消息
     * @return array
     */
    public function sendTextMsg($msg, $robwxid = null, $to_wxid = null)
    {

        $data = array();
        $data['type'] = 100;
        $data['msg'] = urlencode($msg); // 发送内容
        $data['to_wxid'] = $to_wxid ?: $this->from_wxid;     // 对方id
        $data['robot_wxid'] = $robwxid ?: $this->robot_wxid;  // 账户id，用哪个账号去发送这条消息
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'post');
    }


    /**
     * 发送群消息并艾特某人
     *
     * @access public
     * @param string $robwxid 账户id，用哪个账号去发送这条消息
     * @param string $group_wxid 群id
     * @param string $at_wxid 艾特的id，群成员的id
     * @param string $at_name 艾特的昵称，群成员的昵称
     * @param string $msg 消息内容
     * @return array
     */
    public function sendGroupAtMsg($robwxid, $group_wxid, $at_wxid, $at_name, $msg)
    {
        $data = array();
        $data['type'] = 102;
        $data['msg'] = urlencode($msg);
        $data['to_wxid'] = $group_wxid;
        $data['at_wxid'] = $at_wxid;
        $data['at_name'] = $at_name;
        $data['robot_wxid'] = $robwxid;
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'post');
    }


    /**
     * 发送图片消息
     *
     * @access public
     * @param string $img_url 图片的绝对路径，可以是本地图片地址/网络图片地址
     * @param string $robwxid 登录账号id，用哪个账号去发送这条消息,不传则使用当前机器人ID
     * @param string $to_wxid 对方的id，可以是群或者好友id，不传则表示回复当前消息
     * @return array
     */
    public function sendImageMsg($img_url, $robwxid = null, $to_wxid = null)
    {

        $data = array();
        $data['type'] = 103;
        $data['msg'] = $img_url;
        $data['to_wxid'] = $to_wxid ?: $this->from_wxid;
        $data['robot_wxid'] = $robwxid ?: $this->robot_wxid;
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'post');
    }


    /**
     * 发送视频消息
     *
     * @access public
     * @param string $mp4_path 视频文件的绝对路径
     * @param string $robwxid 账户id，用哪个账号去发送这条消息
     * @param string $to_wxid 对方的id，可以是群或者好友id
     * @return array
     */
    public function sendVideoMsg($mp4_path, $robwxid = null, $to_wxid = null)
    {

        $data = array();
        $data['type'] = 104;
        $data['msg'] = $mp4_path;
        $data['to_wxid'] = $to_wxid ?: $this->from_wxid;
        $data['robot_wxid'] = $robwxid ?: $this->robot_wxid;
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'post');
    }


    /**
     * 发送文件消息
     *
     * @access public
     * @param string $file_path 文件的绝对路径
     * @param string $robwxid 账户id，用哪个账号去发送这条消息
     * @param string $to_wxid 对方的id，可以是群或者好友id
     * @return array
     */
    public function sendFileMsg($file_path, $robwxid = null, $to_wxid = null)
    {

        $data = array();
        $data['type'] = 105;
        $data['msg'] = $file_path;           // 发送的文件的绝对路径
        $data['to_wxid'] = $to_wxid ?: $this->from_wxid;     // 对方id（默认发送至来源的id，也可以发给其他人）
        $data['robot_wxid'] = $robwxid ?: $this->robot_wxid;  // 账户id，用哪个账号去发送这条消息
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'post');
    }


    /**
     * 发送动态表情
     *
     * @access public
     * @param string $path 动态表情文件（通常是gif）的绝对路径
     * @param string $robwxid 账户id，用哪个账号去发送这条消息
     * @param string $to_wxid 对方的id，可以是群或者好友id
     * @return array
     */
    public function sendEmojiMsg($path, $robwxid = null, $to_wxid = null)
    {

        $data = array();
        $data['type'] = 106;
        $data['msg'] = $path;           // 发送的动态表情的绝对路径
        $data['to_wxid'] = $to_wxid ?: $this->from_wxid;     // 对方id（默认发送至来源的id，也可以发给其他人）
        $data['robot_wxid'] = $robwxid ?: $this->robot_wxid;  // 账户id，用哪个账号去发送这条消息
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'post');
    }


    /**
     * 发送分享链接
     *
     * @access public
     * @param string $title 链接标题
     * @param string $text 链接内容
     * @param string $target_url 跳转链接
     * @param string $pic_url 图片链接
     * @param string $robwxid 账户id，用哪个账号去发送这条消息
     * @param string $to_wxid 对方的id，可以是群或者好友id
     * @return array
     */
    public function sendLinkMsg($title, $text, $target_url, $pic_url, $robwxid = null, $to_wxid = null)
    {

        // 封装链接结构体
        $link = array();
        $link['title'] = $title;
        $link['text'] = $text;
        $link['url'] = $target_url;
        $link['pic'] = $pic_url;


        $data = array();
        $data['type'] = 107;
        $data['msg'] = $link;           // 发送的分享链接结构体
        $data['to_wxid'] = $to_wxid ?: $this->from_wxid;
        $data['robot_wxid'] = $robwxid ?: $this->robot_wxid;
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'post');
    }


    /**
     * 发送音乐分享
     *
     * @access public
     * @param string $name 歌曲名字
     * @param string $robwxid 账户id，用哪个账号去发送这条消息
     * @param string $to_wxid 对方的id，可以是群或者好友id
     * @return array
     */
    public function sendMusicMsg($name, $robwxid = null, $to_wxid = null)
    {
        $data = array();
        $data['type'] = 108;
        $data['msg'] = $name;
        $data['to_wxid'] = $to_wxid ?: $this->from_wxid;
        $data['robot_wxid'] = $robwxid ?: $this->robot_wxid;
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'post');
    }


    /**
     * 取指定登录账号的昵称
     *
     * @access public
     * @param string $robwxid 账户id
     * @return array 账号昵称
     */
    public function getRobotName($robwxid = null)
    {

        $data = array();
        $data['type'] = 201;
        $data['robot_wxid'] = $robwxid ?: $this->robot_wxid;
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'post');
    }


    /**
     * 取指定登录账号的头像
     *
     * @access public
     * @param string $robwxid 账户id
     * @return array 头像http地址
     */
    public function getRobotHeadImageUrl($robwxid = null)
    {

        $data = array();
        $data['type'] = 202;
        $data['robot_wxid'] = $robwxid ?: $this->robot_wxid;  // 账户id
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'post');
    }


    /**
     * 取登录账号列表
     *
     * @access public
     * @return array 当前框架已登录的账号信息列表
     */
    public function getLoggedAccount_list()
    {

        $data = array();
        $data['type'] = 203;
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'post');
    }


    /**
     * 取好友列表
     *
     * @access public
     * @param string $robwxid 账户id
     * @param int $is_refresh 是否刷新
     * @return array 当前框架已登录的账号信息列表
     */
    public function getFriendList($robwxid = '', $is_refresh = 0)
    {

        $data = array();
        $data['type'] = 204;
        $data['robot_wxid'] = $robwxid;     // 账户id（可选，如果填空字符串，即取所有登录账号的好友列表，反正取指定账号的列表）
        $data['is_refresh'] = $is_refresh;  // 是否刷新列表，0 从缓存获取 / 1 刷新并获取
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'post');
    }


    /**
     * 取群聊列表
     *
     * @access public
     * @param string $robwxid 账户id
     * @param int $is_refresh 是否刷新
     * @return array 当前框架已登录的账号信息列表
     */
    public function getGroupList($robwxid = '', $is_refresh = 0)
    {

        $data = array();
        $data['type'] = 205;
        $data['robot_wxid'] = $robwxid;     // 账户id（可选，如果填空字符串，即取所有登录账号的好友列表，反正取指定账号的列表）
        $data['is_refresh'] = $is_refresh;  // 是否刷新列表，0 从缓存获取 / 1 刷新并获取
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'post');
    }


    /**
     * 取群成员列表
     *
     * @access public
     * @param string $robwxid 账户id
     * @param string $group_wxid 群id
     * @param int $is_refresh 是否刷新
     * @return array 当前框架已登录的账号信息列表
     */
    public function getGroupMemberList($robwxid, $group_wxid, $is_refresh = 0)
    {

        $data = array();
        $data['type'] = 206;
        $data['robot_wxid'] = $robwxid;     // 账户id
        $data['group_wxid'] = $group_wxid;  // 群id
        $data['is_refresh'] = $is_refresh;  // 是否刷新列表，0 从缓存获取 / 1 刷新并获取
        $response = array('data' => json_encode($data));
        $result = $this->sendRequest($response, 'post');
        return json_decode($result, true);
    }


    /**
     * 取群成员资料
     *
     * @access public
     * @param string $robwxid 账户id
     * @param string $group_wxid 群id
     * @param string $member_wxid 群成员id
     * @return array
     */
    public function getGroupMember($robwxid, $group_wxid, $member_wxid)
    {

        $data = array();
        $data['type'] = 207;
        $data['robot_wxid'] = $robwxid;       // 账户id，取哪个账号的资料
        $data['group_wxid'] = $group_wxid;    // 群id
        $data['member_wxid'] = $member_wxid;  // 群成员id
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'post');
    }


    /**
     * 接收好友转账
     *
     * @access public
     * @param string $robwxid 账户id
     * @param string $friend_wxid 朋友id
     * @param $json_string
     * @return array
     */
    public function acceptTransfer($robwxid = null, $friend_wxid = null, $json_string = null)
    {

        $data = array();
        $data['type'] = 301;
        $data['robot_wxid'] = $robwxid ?: $this->robot_wxid;      // 账户id
        $data['friend_wxid'] = $friend_wxid ?: $this->from_wxid;  // 朋友id
        $data['msg'] = $json_string ?: $this->msg;         // 转账事件原消息
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'post');
    }


    /**
     * 同意群聊邀请
     *
     * @access public
     * @param string $robwxid 账户id
     * @param string $json_string 同步消息事件中群聊邀请原消息
     * @return array
     */
    public function agreeGroupInvite($robwxid = null, $json_string = null)
    {

        $data = array();
        $data['type'] = 302;
        $data['robot_wxid'] = $robwxid ?: $this->robot_wxid;      // 账户id
        $data['msg'] = $json_string ?: $this->msg;         // 同步消息事件中群聊邀请原消息
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'post');
    }


    /**
     * 同意好友请求
     *
     * @access public
     * @param string $robwxid 账户id
     * @param string $json_string 好友请求事件中原消息
     * @return array
     */
    public function agreeFriendVerify($robwxid = null, $json_string = null)
    {

        $data = array();
        $data['type'] = 303;
        $data['robot_wxid'] = $robwxid ?: $this->robot_wxid;      // 账户id
        $data['msg'] = $json_string ?: $this->msg;         // 好友请求事件中原消息
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'post');
    }

    /**
     * 修改好友备注
     *
     * @access public
     * @param string $robwxid 机器人账户id
     * @param string $friend_wxid 好友id
     * @param string $note 新备注（空字符串则是删除备注）
     * @return array
     */
    public function setFriendNote($robwxid, $friend_wxid, $note)
    {

        $data = array();
        $data['type'] = 304;
        $data['robot_wxid'] = $robwxid;
        $data['friend_wxid'] = $friend_wxid;
        $data['note'] = $note;
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'post');
    }


    /**
     * 删除好友
     *
     * @access public
     * @param string $robwxid 机器人账户id
     * @param string $friend_wxid 好友id
     * @return array
     */
    public function deleteFriend($robwxid, $friend_wxid)
    {

        $data = array();
        $data['type'] = 305;
        $data['robot_wxid'] = $robwxid;
        $data['friend_wxid'] = $friend_wxid;
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'post');
    }


    /**
     * 踢出群成员
     *
     * @access public
     * @param string $member_wxid 群成员id
     * @param string $group_wxid 群id
     * @param string $robwxid 账户id
     * @return array|string
     */
    public function removeGroupMember($member_wxid, $group_wxid = null, $robwxid = null)
    {

        $data = array();
        $data['type'] = 306;
        $data['robot_wxid'] = $robwxid ?: $this->robot_wxid;
        $data['group_wxid'] = $group_wxid ?: $this->from_wxid;
        $data['member_wxid'] = $member_wxid;
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'post');
    }


    /**
     * 修改群名称
     *
     * @access public
     * @param string $robwxid 账户id
     * @param string $group_wxid 群id
     * @param string $group_name 新群名
     * @return array
     */
    public function setGroupName($robwxid, $group_wxid, $group_name)
    {
        $data = array();
        $data['type'] = 307;
        $data['robot_wxid'] = $robwxid;      // 账户id
        $data['group_wxid'] = $group_wxid;  // 群id
        $data['group_name'] = $group_name;   // 新群名
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'post');
    }

    /**
     * 修改群公告
     *
     * @access public
     * @param string $robwxid 账户id
     * @param string $group_wxid 群id
     * @param string $notice 新公告
     * @return array
     */
    public function setGroupNotice($robwxid, $group_wxid, $notice)
    {

        $data = array();
        $data['type'] = 308;
        $data['robot_wxid'] = $robwxid;      // 账户id
        $data['group_wxid'] = $group_wxid;  // 群id
        $data['notice'] = $notice;       // 新公告
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'post');
    }


    /**
     * 建立新群
     *
     * @access public
     * @param string $robwxid 账户id
     * @param array $friends 三个人及以上的好友id数组，['wxid_1xxx', 'wxid_2xxx', 'wxid_3xxx', 'wxid_4xxx']
     * @return array
     */
    public function creatGroup($robwxid, array $friends)
    {
        $data = array();
        $data['type'] = 309;
        $data['robot_wxid'] = $robwxid;  // 账户id
        $data['friends'] = $friends;  // 好友id数组
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'post');
    }


    /**
     * 退出群聊
     *
     * @access public
     * @param string $robwxid 账户id
     * @param string $group_wxid 群id
     * @return array
     */
    public function quitGroup($robwxid, $group_wxid)
    {
        $data = array();
        $data['type'] = 310;
        $data['robot_wxid'] = $robwxid;    // 账户id
        $data['group_wxid'] = $group_wxid; // 群id
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'post');
    }

    /**
     * 邀请加入群聊
     *
     * @access public
     * @param string $robwxid 账户id
     * @param string $group_wxid 群id
     * @param string $friend_wxid 好友id
     * @return array
     */
    public function inviteInGroup($robwxid, $group_wxid, $friend_wxid)
    {
        $data = array();
        $data['type'] = 311;
        $data['robot_wxid'] = $robwxid;
        $data['group_wxid'] = $group_wxid;
        $data['friend_wxid'] = $friend_wxid;
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'post');
    }


    /**
     * 执行一个 HTTP 请求，仅仅是post组件，其他语言请自行替换即可
     *
     * @param mixed $params 表单参数
     * @param int $timeout 超时时间
     * @param string $method 请求方法 post / get
     * @return array|string
     */
    public function sendRequest($params, $method = 'get', $timeout = 3)
    {
        $curl = curl_init();
        $is_https = stripos($this->url, 'https://') === 0 ? true : false;
        if ('get' == $method) {//以GET方式发送请求
            curl_setopt($curl, CURLOPT_URL, $this->url);
        } else {//以POST方式发送请求
            curl_setopt($curl, CURLOPT_URL, $this->url);
            curl_setopt($curl, CURLOPT_POST, 1);//post提交方式
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);//设置传送的参数
        }

        curl_setopt($curl, CURLOPT_HEADER, false);//设置header
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);//要求结果为字符串且输出到屏幕上
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);//设置等待时间
        if ($is_https) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
        }
        $res = curl_exec($curl);//运行curl
        $err = curl_error($curl);

        if (false === $res || !empty($err)) {
//            $Errno = curl_errno($curl);
//            $Info = curl_getinfo($curl);
//            curl_close($curl);
            return false;//$err . ' result: ' . $res . 'error_msg: ' . $Errno;
        }
        curl_close($curl);//关闭curl
        $res = json_decode($res, true);
        $data = json_decode($res['data'], true);
        if ($data) {
            $res = $data;
        } else {
            $res = urldecode($res['data']);
        }
        return $res;
    }
}