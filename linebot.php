<?php


//ユーザーからのメッセージ取得
$json_string = file_get_contents('php://input');
$json_object = json_decode($json_string);

//取得データ
$replyToken = $json_object->{"events"}[0]->{"replyToken"};        //返信用トークン
$message_type = $json_object->{"events"}[0]->{"message"}->{"type"};    //メッセージタイプ
if ($message_type == "text") {
    $message_text = $json_object->{"events"}[0]->{"message"}->{"text"};    //メッセージ内容
}



//メッセージタイプが「text」のときは適当な値を返すそれ以外の時は決まった文章を返す
// if ($message_type == "text") {
//     //返信メッセージ
//     if ($message_text == "scala") {
//       $return_message_text = sending_local($post_data);
//     }else {
//       $return_message_text = $message_text . "←とはどういう意味ですか？";
//     }
//
// } elseif ($message_type == "sticker") {
//     $return_message_text = "そのスタンプかわいいね！";
//     $message_type = "text";
// } else {
//     $return_message_text = "それいいね！？";
//     $message_type = "text";
// }

if ($message_type == "text") {
  //返信メッセージ
  $post_data = [
      "text_type" => $message_type,
      "text_body" => $message_text
  ];
  $return_message_text = sending_local($post_data);

} elseif ($message_type == "sticker") {
    $return_message_text = "そのスタンプかわいいね！";
    $message_type = "text";
} else {
    $return_message_text = "それいいね！？";
    $message_type = "text";
}

require_once('keys.php');


//返信実行
sending_messages(getKey(), $replyToken, $message_type, $return_message_text);
?>


<?php
//メッセージの送信
function sending_messages($accessToken, $replyToken, $message_type, $return_message_text)
{
    //レスポンスフォーマット
    $response_format_text = [
        "type" => $message_type,
        "text" => $return_message_text
    ];

    //ポストデータ
    $post_data = [
        "replyToken" => $replyToken,
        "messages" => [$response_format_text]
    ];

    //curl実行
    $ch = curl_init("https://api.line.me/v2/bot/message/reply");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charser=UTF-8',
        'Authorization: Bearer ' . $accessToken
    ));
    $result = curl_exec($ch);
    curl_close($ch);
}

?>

<?php
function sending_local($post_data)
{
    //curl実行
    // $ch_local = curl_init("http://localhost:9000/line/request");
    $ch_local = curl_init("http://localhost:9000/talk/simple");
    curl_setopt($ch_local, CURLOPT_POST, true);
    curl_setopt($ch_local, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch_local, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch_local, CURLOPT_POSTFIELDS, json_encode($post_data));
    curl_setopt($ch_local, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charser=UTF-8',
    ));

    //レスポンスbodyを取得
    $response = curl_exec($ch_local);
    curl_close($ch_local);
    $header_size = curl_getinfo($ch_local, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $header_size);
    $result = substr($response, $header_size);

    return $result;
}

?>
