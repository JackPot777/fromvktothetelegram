<?php
$access_token = 'Токен вк';
$tg_token = 'Токен бота телеграм';
$tg_chatId = 'ID чата в телеграм (если бот будет писать вам то ID ваш';
date_default_timezone_set ('Europe/Moscow');
//Получаем массив сообщений 
$messagesGet = curl('https://api.vk.com/method/messages.get?version=5.4&count=200&filters=1&access_token='.$access_token);
$jsonM = json_decode($messagesGet,1);
//Текст ответа 
$texed = array('Привет, меня сейчас нет на месте, но я скоро буду. Если это что-то очень срочное, позвоните мне.');
$chbade = mt_rand (0, count($texed)-1);
$text = urlencode($texed[$chbade]);
$countMess = $jsonM['response']['0'];
$uids = array('jmg');
for($i=1;$i<=$countMess;$i++){
  $senderUid = $jsonM['response'][$i]['uid'];
  $uids[$i] = $senderUid;
}
$uids = array_values(array_unique($uids));
for($q=1;$q<=count($uids)-1;$q++){
echo $uids[$q].'<br>';
//Получаем имена и сообщения чтобы отправить в телеграм
$usersnamesGet = curl('https://api.vk.com/method/users.get?version=5.4&lang=0&name_case=gen&user_ids='.$uids[$q]);
$messagesGet = curl('https://api.vk.com/method/messages.get?version=5.4&access_token='.$access_token);
$jsonLN = json_decode($usersnamesGet,1);
$jsonMG = json_decode($messagesGet,1);
$sendName = $jsonLN['response'][0]['first_name'].' '.$jsonLN['response'][0]['last_name'].':%0A%0D';
// Выбираем шаблон исходя из типа сообщения (текст, фото, аудио, видео)
// ! Функционал не доработан, если отправляют несколько фото придет только первая ! 
if ($sendMessageText = $jsonMG['response'][1]['body'] != '') {
  $sendMessageText = $jsonMG['response'][1]['body'];
}else{
  $sendMessageText = '';
}
if ($jsonMG['response'][1]['attachment']['photo']['src_big'] != '') {
  $sendPhoto = $jsonMG['response'][1]['attachment']['photo']['src_big'].'%0A%0D';
}else{
  $sendPhoto = '';
}
if ($jsonMG['response'][1]['attachment']['video']['vid'] != '') {
  $sendVideo = 'https://vk.com/video'.$jsonMG['response'][1]['attachment']['video']['owner_id'].'_'.$jsonMG['response'][1]['attachment']['video']['vid'].'%0A%0D';
}else{
  $sendVideo = '';
}
if ($jsonMG['response'][1]['attachment']['audio']['aid'] != '') {
  $sendAudio = 'https://music7s.me/song/'.$jsonMG['response'][1]['attachment']['audio']['owner_id'].'_'.$jsonMG['response'][1]['attachment']['audio']['aid'].'%0A%0D';
}else{
  $sendAudio = '';
}
// Страница скрипта ответа, для инлайн кнопки
$answerURL = "answervk.php?uid=".$uids[$q];
$inline_button = array("text"=>"Ответить","url"=>$answerURL);
$inline_keyboard = [[$inline_button]];
$keyboard=array("inline_keyboard"=>$inline_keyboard);
$replyMarkup = json_encode($keyboard);
// Отмечаем сообщение прочитаным, так же можно отвечать, если использовать метод send 
echo curl('https://api.vk.com/method/messages.markAsRead?version=5.4&peer_id='.$uids[$q].'&access_token='.$access_token);
// Отправляем сообщение в телеграм. 
echo curl('https://api.telegram.org/bot'.$tg_token.'/sendMessage?chat_id='.$tg_chatId.'&text=Вам сообщение от '.$sendName.$sendMessageText.$sendPhoto.$sendVideo.$sendAudio.'&reply_markup='.$replyMarkup);
}
function curl($url){
    $ch = curl_init( $url );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
    $response = curl_exec( $ch );
    curl_close( $ch );
    return $response;
  }
?>