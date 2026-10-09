<?php
const ADMIN_MAIL='info@build3.com',MAIL_FROM='no-reply@build3.com';
$isA=($_GET['s']??'')=='a';$rm=$isA&&($_COOKIE['b3rm']??'')=='1';$life=($isA&&$rm)?2592000:0;ini_set('session.gc_maxlifetime',2592000);session_name($isA?'B3ADMIN':'B3SITE');
session_set_cookie_params(['lifetime'=>$life,'path'=>'/','httponly'=>true,'samesite'=>'Lax','secure'=>!empty($_SERVER['HTTPS'])]);
session_start();if($rm)setcookie(session_name(),session_id(),['expires'=>time()+$life,'path'=>'/','httponly'=>true,'samesite'=>'Lax','secure'=>!empty($_SERVER['HTTPS'])]);header('Content-Type:application/json');error_reporting(0);
try{$db=new PDO('mysql:host=localhost;dbname=build3;charset=utf8mb4','root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);}catch(Exception $x){die('{"error":"db"}');}
if(!$db->query("SHOW TABLES LIKE 'chat'")->fetch())$db->exec("CREATE TABLE chat(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL,sender ENUM('client','admin') NOT NULL,admin_id INT NULL,body TEXT,file VARCHAR(200) NULL,seen TINYINT NOT NULL DEFAULT 0,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX(user_id,id),FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE)");
if(!$db->query("SHOW COLUMNS FROM messages LIKE 'reply'")->fetch())$db->exec("ALTER TABLE messages ADD reply TEXT NULL, ADD replied_at DATETIME NULL, ADD reply_seen TINYINT NOT NULL DEFAULT 1");
if(!$db->query("SHOW COLUMNS FROM comments LIKE 'approved'")->fetch()){$db->exec("ALTER TABLE comments ADD approved TINYINT NOT NULL DEFAULT 0");$db->exec("UPDATE comments SET approved=1");}
function out($d){echo json_encode($d,JSON_UNESCAPED_UNICODE);exit;}
function q($s,$p=[]){global $db;$t=$db->prepare($s);$t->execute($p);return $t;}
function all($s,$p=[]){return q($s,$p)->fetchAll();}
function one($s,$p=[]){return q($s,$p)->fetch();}
function cnt($s){return (int)one("SELECT COUNT(*) c FROM $s")['c'];}
function ip(){return $_SERVER['REMOTE_ADDR']??'';}
function blocked($kind,$k,$max,$min){global $db;return cnt("attempts WHERE kind=".$db->quote($kind)." AND k=".$db->quote($k)." AND created_at>NOW()-INTERVAL ".(int)$min." MINUTE")>=$max;}
function hit($kind,$k){q("INSERT INTO attempts(kind,k,ip) VALUES(?,?,?)",[$kind,$k,ip()]);}
function mailx($to,$sub,$body){return @mail($to,'=?UTF-8?B?'.base64_encode($sub).'?=',$body,"From: Build 3 <".MAIL_FROM.">\r\nContent-Type: text/plain; charset=UTF-8");}
function base(){return (!empty($_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['SCRIPT_NAME']),'/').'/';}
function need($adm=0){global $ad,$u;if(!$u||$ad!=$adm){http_response_code(403);out(['error'=>'auth']);}}
function save($f,$img){if($f['error']!==0)return'';if($f['size']>($img?3:6)*1048576)out(['error'=>'big']);
 if($img){$i=@getimagesize($f['tmp_name']);$m=[IMAGETYPE_JPEG=>'jpg',IMAGETYPE_PNG=>'png',IMAGETYPE_GIF=>'gif',IMAGETYPE_WEBP=>'webp'];if(!$i||!isset($m[$i[2]]))out(['error'=>'type']);$x=$m[$i[2]];}
 else{$x=strtolower(pathinfo($f['name'],PATHINFO_EXTENSION));if(!in_array($x,['jpg','jpeg','png','gif','webp','pdf','doc','docx','zip','rar','txt']))out(['error'=>'type']);}
 @mkdir('uploads');$n='uploads/'.bin2hex(random_bytes(8)).'.'.$x;move_uploaded_file($f['tmp_name'],$n);return $n;}
function up($k,$img=1){return (empty($_FILES[$k]['name'])||is_array($_FILES[$k]['name']))?'':save($_FILES[$k],$img);}
function ups($k){$o=[];$f=$_FILES[$k]??null;if(!$f||!is_array($f['name']))return $o;foreach($f['name'] as $i=>$n){if(!$n)continue;$o[]=save(['name'=>$n,'tmp_name'=>$f['tmp_name'][$i],'size'=>$f['size'][$i],'error'=>$f['error'][$i]],1);}return $o;}
$a=$_GET['action']??'';if(isset($_SESSION['out'])&&$a!='closing'){if($_SESSION['out']<time())unset($_SESSION['u']);unset($_SESSION['out']);}$P=array_map('trim',array_filter($_POST,'is_string'));$u=$_SESSION['u']??null;$ad=($u&&$u['role']=='admin')?1:0;
if(rand(1,60)==1)q("DELETE FROM attempts WHERE created_at<NOW()-INTERVAL 1 DAY");
if($u)q("UPDATE ".($ad?'admins':'users')." SET last_seen=NOW() WHERE id=?",[$u['id']]);
$on="last_seen>NOW()-INTERVAL 40 SECOND";$hp=fn($s)=>password_hash($s,PASSWORD_DEFAULT);
switch($a){
case'register':
 if(blocked('reg',ip(),10,60))out(['error'=>'blocked']);
 if(!$P['name'])out(['error'=>'name']);if(!filter_var($P['email'],FILTER_VALIDATE_EMAIL))out(['error'=>'email']);if(strlen($P['password'])<4)out(['error'=>'short']);
 if(one("SELECT id FROM users WHERE email=?",[$P['email']]))out(['error'=>'exists']);hit('reg',ip());
 q("INSERT INTO users(name,email,phone,pass) VALUES(?,?,?,?)",[mb_substr($P['name'],0,100),$P['email'],mb_substr($P['phone'],0,30),$hp($P['password'])]);
 session_regenerate_id(true);$_SESSION['u']=['id'=>$db->lastInsertId(),'name'=>$P['name'],'role'=>'client'];out(['ok'=>1,'u'=>$_SESSION['u']]);
case'login':case'a_login':
 $adm=$a=='a_login';$k=strtolower($P['email']).'|'.ip();$kd=$adm?'alogin':'login';if(blocked($kd,$k,5,15))out(['error'=>'blocked']);
 $r=one("SELECT id,name,pass FROM ".($adm?'admins':'users')." WHERE email=?",[$P['email']]);
 if(!$r||!password_verify($P['password'],$r['pass'])){hit($kd,$k);out(['error'=>'bad']);}
 q("DELETE FROM attempts WHERE kind=? AND k=?",[$kd,$k]);session_regenerate_id(true);$_SESSION['u']=['id'=>$r['id'],'name'=>$r['name'],'role'=>$adm?'admin':'client'];
 if($adm){$rem=!empty($P['remember']);$ck=['path'=>'/','httponly'=>true,'samesite'=>'Lax','secure'=>!empty($_SERVER['HTTPS'])];setcookie('b3rm',$rem?'1':'',['expires'=>$rem?time()+2592000:time()-3600]+$ck);setcookie(session_name(),session_id(),['expires'=>$rem?time()+2592000:0]+$ck);}
 out(['ok'=>1,'u'=>$_SESSION['u']]);
case'forgot':if(blocked('fg',ip(),5,60))out(['error'=>'blocked']);if(!filter_var($P['email'],FILTER_VALIDATE_EMAIL))out(['error'=>'email']);hit('fg',ip());$x=one("SELECT id,email FROM users WHERE email=?",[$P['email']]);if(!$x)out(['error'=>'nouser']);
 $t=bin2hex(random_bytes(32));q("DELETE FROM resets WHERE user_id=?",[$x['id']]);q("INSERT INTO resets(user_id,token,expires) VALUES(?,?,NOW()+INTERVAL 1 HOUR)",[$x['id'],hash('sha256',$t)]);
 if(!mailx($x['email'],'إعادة تعيين كلمة المرور - Build 3',"اضغط على اللينك لتعيين كلمة مرور جديدة (صالح لمدة ساعة):\n".base()."index.html?reset=$t"))out(['error'=>'mailfail']);out(['ok'=>1]);
case'reset':if(strlen($P['password'])<4)out(['error'=>'short']);$x=one("SELECT user_id FROM resets WHERE token=? AND expires>NOW()",[hash('sha256',$P['token'])]);if(!$x)out(['error'=>'expired']);q("UPDATE users SET pass=? WHERE id=?",[$hp($P['password']),$x['user_id']]);q("DELETE FROM resets WHERE user_id=?",[$x['user_id']]);out(['ok'=>1]);
case'logout':session_destroy();setcookie('b3rm','',['expires'=>time()-3600,'path'=>'/']);out(['ok'=>1]);
case'me':if(!$u)out(['u'=>null]);out(['u'=>array_merge($u,$ad?one("SELECT title,photo FROM admins WHERE id=?",[$u['id']]):[])]);
case'closing':if($u){if(!$ad)q("UPDATE users SET last_seen=NULL WHERE id=?",[$u['id']]);if(!$rm)$_SESSION['out']=time()+6;}out(['ok'=>1]);
case'contact':need();$k='u'.$u['id'];if(blocked('msg',$k,5,60))out(['error'=>'blocked']);if(mb_strlen($P['body'])<5)out(['error'=>'msg']);$us=one("SELECT name,email,phone FROM users WHERE id=?",[$u['id']]);hit('msg',$k);
 q("INSERT INTO messages(name,email,phone,body) VALUES(?,?,?,?)",[$us['name'],$us['email'],$us['phone'],mb_substr($P['body'],0,1500)]);mailx(ADMIN_MAIL,'رسالة جديدة من الموقع',"من: {$us['name']} <{$us['email']}>\n\n{$P['body']}");out(['ok'=>1]);
case'stats':out(['clients'=>cnt("users"),'projects'=>cnt("projects"),'done'=>cnt("requests WHERE status='done'"),'avg'=>(float)one("SELECT IFNULL(ROUND(AVG(rating),1),0) a FROM comments WHERE rating>0 AND approved=1")['a'],'rc'=>cnt("comments WHERE rating>0 AND approved=1")]);
case'projects':$p=all("SELECT * FROM projects ORDER BY id DESC");$im=all("SELECT project_id,image FROM project_images ORDER BY id");foreach($p as &$r){$r['imgs']=array_values(array_column(array_filter($im,fn($i)=>$i['project_id']==$r['id']),'image'));if(!$r['imgs'])$r['imgs']=[$r['image']];}out($p);
case'team':out(all("SELECT name,title,photo FROM admins"));
case'comments':out(all("SELECT c.id,c.text,c.rating,c.created_at,u.name FROM comments c JOIN users u ON u.id=c.user_id WHERE c.approved=1 ORDER BY c.id DESC LIMIT 40"));
case'comment':need();if(!$P['text'])out(['error'=>'text']);q("INSERT INTO comments(user_id,text,rating) VALUES(?,?,?)",[$u['id'],mb_substr($P['text'],0,500),max(1,min(5,(int)$P['rating']))]);out(['ok'=>1]);
case'request':need();if(!$P['details'])out(['error'=>'details']);$sv=in_array($P['service'],['Web','Mobile','Design'])?$P['service']:'Web';$f=up('file',0);
 q("INSERT INTO requests(user_id,service,details,budget,days,file) VALUES(?,?,?,?,?,?)",[$u['id'],$sv,mb_substr($P['details'],0,2000),mb_substr($P['budget'],0,40),(int)$P['days']?:null,$f?:null]);mailx(ADMIN_MAIL,'طلب جديد من '.$u['name'],"خدمة: $sv\nميزانية: {$P['budget']}\n\n{$P['details']}");out(['ok'=>1]);
case'mine':need();out(all("SELECT * FROM requests WHERE user_id=? ORDER BY id DESC",[$u['id']]));
case'c_reply':need();$x=one("SELECT id FROM requests WHERE id=? AND user_id=? AND neg='company' AND status<>'done'",[$P['id'],$u['id']]);if(!$x)out(['error'=>'invalid']);
 if($P['act']=='accept')q("UPDATE requests SET budget=offer,neg='agreed' WHERE id=?",[$x['id']]);
 else{if(!$P['price'])out(['error'=>'invalid']);q("UPDATE requests SET budget=?,offer=NULL,neg='client',days=IFNULL(?,days) WHERE id=?",[mb_substr($P['price'],0,40),(int)$P['days']?:null,$x['id']]);}
 out(['ok'=>1]);
case'my_msgs':need();$em=one("SELECT email FROM users WHERE id=?",[$u['id']])['email'];q("UPDATE messages SET reply_seen=1 WHERE email=?",[$em]);out(all("SELECT id,body,reply,replied_at,created_at FROM messages WHERE email=? ORDER BY id DESC",[$em]));
case'msgs_unread':need();out(['n'=>(int)one("SELECT COUNT(*) c FROM messages WHERE email=(SELECT email FROM users WHERE id=?) AND reply IS NOT NULL AND reply_seen=0",[$u['id']])['c']]);
case'chat_msgs':need();q("UPDATE chat SET seen=1 WHERE user_id=? AND sender='admin'",[$u['id']]);out(['msgs'=>array_reverse(all("SELECT c.id,c.sender,c.body,c.file,c.seen,c.created_at,(SELECT name FROM admins WHERE id=c.admin_id) an FROM chat c WHERE c.user_id=? ORDER BY c.id DESC LIMIT 200",[$u['id']])),'team'=>cnt("admins WHERE $on")]);
case'chat_send':need();if(blocked('chat','u'.$u['id'],30,10))out(['error'=>'blocked']);if(!$P['body']&&empty($_FILES['file']['name']))out(['error'=>'invalid']);$f=up('file',0);$pv=cnt("chat WHERE user_id=".(int)$u['id']." AND sender='client' AND seen=0");hit('chat','u'.$u['id']);
 q("INSERT INTO chat(user_id,sender,body,file) VALUES(?,'client',?,?)",[$u['id'],mb_substr($P['body'],0,1000),$f?:null]);if(!$pv)mailx(ADMIN_MAIL,'رسالة شات جديدة من '.$u['name'],$P['body']?:'(مرفق)');out(['ok'=>1]);
case'chat_unread':need();out(['n'=>cnt("chat WHERE user_id=".(int)$u['id']." AND sender='admin' AND seen=0")]);
case'a_chats':need(1);out(all("SELECT u.id,u.name,u.email,IFNULL(u.last_seen>NOW()-INTERVAL 40 SECOND,0) online,(SELECT body FROM chat WHERE user_id=u.id ORDER BY id DESC LIMIT 1) lb,(SELECT file FROM chat WHERE user_id=u.id ORDER BY id DESC LIMIT 1) lf,(SELECT created_at FROM chat WHERE user_id=u.id ORDER BY id DESC LIMIT 1) lt,(SELECT COUNT(*) FROM chat WHERE user_id=u.id AND sender='client' AND seen=0) un FROM users u WHERE EXISTS(SELECT 1 FROM chat WHERE user_id=u.id) ORDER BY lt DESC"));
case'a_chat':need(1);q("UPDATE chat SET seen=1 WHERE user_id=? AND sender='client'",[$P['id']]);out(['u'=>one("SELECT id,name,email,IFNULL(last_seen>NOW()-INTERVAL 40 SECOND,0) online FROM users WHERE id=?",[$P['id']]),'msgs'=>array_reverse(all("SELECT c.id,c.sender,c.body,c.file,c.seen,c.created_at,(SELECT name FROM admins WHERE id=c.admin_id) an FROM chat c WHERE c.user_id=? ORDER BY c.id DESC LIMIT 300",[$P['id']]))]);
case'a_chatsend':need(1);if(!$P['body']&&empty($_FILES['file']['name']))out(['error'=>'invalid']);$x=one("SELECT email FROM users WHERE id=?",[$P['id']]);if(!$x)out(['error'=>'invalid']);$f=up('file',0);$pv=cnt("chat WHERE user_id=".(int)$P['id']." AND sender='admin' AND seen=0");
 q("INSERT INTO chat(user_id,sender,admin_id,body,file) VALUES(?,'admin',?,?,?)",[$P['id'],$u['id'],mb_substr($P['body'],0,1000),$f?:null]);if(!$pv)mailx($x['email'],'رسالة جديدة من Build 3',($P['body']?:'(مرفق)')."\n\nادخل على الموقع للرد من أيقونة الشات.");out(['ok'=>1]);
case'a_stats':need(1);out(['clients'=>cnt("users"),'orders'=>cnt("requests"),'new'=>cnt("requests WHERE status='new'"),'progress'=>cnt("requests WHERE status='progress'"),'done'=>cnt("requests WHERE status='done'"),'online'=>cnt("users WHERE $on"),'projects'=>cnt("projects"),'comments'=>cnt("comments"),'msgs'=>cnt("messages WHERE is_read=0"),'chat'=>cnt("chat WHERE sender='client' AND seen=0"),'pc'=>cnt("comments WHERE approved=0"),'last'=>(int)one("SELECT IFNULL(MAX(id),0) m FROM requests")['m'],'lastmsg'=>(int)one("SELECT IFNULL(MAX(id),0) m FROM messages")['m'],'lastchat'=>(int)one("SELECT IFNULL(MAX(id),0) m FROM chat WHERE sender='client'")['m'],'today'=>cnt("requests WHERE DATE(created_at)=CURDATE()")]);
case'a_online':need(1);out(all("SELECT name,email,phone,last_seen FROM users WHERE $on ORDER BY last_seen DESC"));
case'a_clients':need(1);out(all("SELECT u.id,u.name,u.email,u.phone,u.created_at,IFNULL($on,0) online,(SELECT COUNT(*) FROM requests WHERE user_id=u.id) n FROM users u ORDER BY id DESC"));
case'a_client':need(1);out(['u'=>one("SELECT id,name,email,phone,created_at,last_seen FROM users WHERE id=?",[$P['id']]),'orders'=>all("SELECT * FROM requests WHERE user_id=? ORDER BY id DESC",[$P['id']])]);
case'a_resetpw':need(1);$pw=substr(bin2hex(random_bytes(4)),0,8);q("UPDATE users SET pass=? WHERE id=?",[$hp($pw),$P['id']]);out(['pw'=>$pw]);
case'a_orders':need(1);$o=empty($P['done'])?'<>':'=';out(all("SELECT r.*,u.name,u.phone FROM requests r JOIN users u ON u.id=r.user_id WHERE r.hid=0 AND r.status $o 'done' ORDER BY r.id DESC"));
case'a_status':need(1);if(!in_array($P['status'],['new','progress','done']))out(['ok'=>1]);
 $x=one("SELECT r.status,r.neg,u.email FROM requests r JOIN users u ON u.id=r.user_id WHERE r.id=?",[$P['id']]);if(!$x||$x['status']=='done')out(['ok'=>1]);
 if($P['status']!='new'&&$x['neg']!='agreed')out(['error'=>'notagreed']);
 if($P['status']=='done'&&!preg_match('~^https?://~i',$P['url']))out(['error'=>'url']);
 q("UPDATE requests SET status=?,delivery_url=? WHERE id=?",[$P['status'],$P['status']=='done'?$P['url']:null,$P['id']]);
 if($P['status']=='done')mailx($x['email'],'طلبك اتنفذ - Build 3',"تم الانتهاء من طلبك. رابط موقعك:\n{$P['url']}\n\nشكراً لثقتك في Build 3");out(['ok'=>1]);
case'a_offer':need(1);if(!$P['price'])out(['error'=>'invalid']);q("UPDATE requests SET offer=?,neg='company',days=IFNULL(?,days) WHERE id=? AND neg<>'agreed' AND status<>'done'",[mb_substr($P['price'],0,40),(int)$P['days']?:null,$P['id']]);out(['ok'=>1]);
case'a_agree':need(1);q("UPDATE requests SET neg='agreed' WHERE id=? AND neg='client' AND status<>'done'",[$P['id']]);out(['ok'=>1]);
case'a_delo':need(1);q("UPDATE requests SET hid=1 WHERE id=? AND status='done'",[$P['id']]);out(['ok'=>1]);
case'a_link':need(1);if(!preg_match('~^https?://~i',$P['url']))out(['error'=>'url']);q("UPDATE requests SET delivery_url=? WHERE id=? AND status='done'",[$P['url'],$P['id']]);out(['ok'=>1]);
case'a_msgreply':need(1);if(mb_strlen($P['body'])<1)out(['error'=>'invalid']);$m=one("SELECT email FROM messages WHERE id=?",[$P['id']]);if(!$m)out(['error'=>'invalid']);q("UPDATE messages SET reply=?,replied_at=NOW(),reply_seen=0,is_read=1 WHERE id=?",[mb_substr($P['body'],0,2000),$P['id']]);mailx($m['email'],'رد على رسالتك - Build 3',$P['body']."\n\nتقدر تشوف الرد في الموقع > الرسائل");out(['ok'=>1]);
case'a_msgs':need(1);out(all("SELECT * FROM messages ORDER BY id DESC"));
case'a_msgread':need(1);q("UPDATE messages SET is_read=1 WHERE id=?",[$P['id']]);out(['ok'=>1]);
case'a_msgdel':need(1);q("DELETE FROM messages WHERE id=?",[$P['id']]);out(['ok'=>1]);
case'a_add':need(1);$im=ups('images');if(!$P['title']||!$im)out(['error'=>'invalid']);if($P['url']&&!preg_match('~^https?://~i',$P['url']))out(['error'=>'url']);
 q("INSERT INTO projects(title,description,image,url) VALUES(?,?,?,?)",[mb_substr($P['title'],0,120),mb_substr($P['description'],0,2000),$im[0],$P['url']]);$id=$db->lastInsertId();foreach($im as $i)q("INSERT INTO project_images(project_id,image) VALUES(?,?)",[$id,$i]);out(['ok'=>1]);
case'a_del':need(1);foreach(all("SELECT image FROM project_images WHERE project_id=?",[$P['id']]) as $i)@unlink($i['image']);q("DELETE FROM projects WHERE id=?",[$P['id']]);out(['ok'=>1]);
case'a_delc':need(1);q("DELETE FROM comments WHERE id=?",[$P['id']]);out(['ok'=>1]);
case'a_approve':need(1);q("UPDATE comments SET approved=? WHERE id=?",[empty($P['v'])?0:1,$P['id']]);out(['ok'=>1]);
case'a_comments':need(1);out(all("SELECT c.id,c.text,c.rating,c.approved,c.created_at,u.name FROM comments c JOIN users u ON u.id=c.user_id ORDER BY c.id DESC"));
case'a_team':need(1);out(all("SELECT id,name,email,title,photo FROM admins ORDER BY id"));
case'a_addadmin':need(1);if(!$P['name']||!filter_var($P['email'],FILTER_VALIDATE_EMAIL))out(['error'=>'invalid']);if(strlen($P['password'])<4)out(['error'=>'short']);if(one("SELECT id FROM admins WHERE email=?",[$P['email']]))out(['error'=>'exists']);
 q("INSERT INTO admins(name,email,pass,title) VALUES(?,?,?,?)",[mb_substr($P['name'],0,100),$P['email'],$hp($P['password']),mb_substr($P['title'],0,60)]);out(['ok'=>1]);
case'a_deladmin':need(1);if($P['id']==$u['id']||cnt("admins")<2)out(['error'=>'invalid']);$x=one("SELECT photo FROM admins WHERE id=?",[$P['id']]);if($x['photo'])@unlink($x['photo']);q("DELETE FROM admins WHERE id=?",[$P['id']]);out(['ok'=>1]);
case'a_profile':need(1);if($P['title']!=='')q("UPDATE admins SET title=? WHERE id=?",[mb_substr($P['title'],0,60),$u['id']]);if($P['password']!==''){if(strlen($P['password'])<4)out(['error'=>'short']);q("UPDATE admins SET pass=? WHERE id=?",[$hp($P['password']),$u['id']]);}if($i=up('photo')){$o=one("SELECT photo FROM admins WHERE id=?",[$u['id']]);if($o['photo'])@unlink($o['photo']);q("UPDATE admins SET photo=? WHERE id=?",[$i,$u['id']]);}out(['ok'=>1]);
}
out(['error'=>'unknown']);
