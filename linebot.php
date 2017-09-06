<?php

$accessToken = 'X20v49IGjBZzfnQ2Ut/pxkysdn47JG+U0665H1LX0s0Y5nN5TLLPs2EZQ0n7BrAPa5Yl5SaxagLOia0+7LEJMA3O22g8tjJ9KbmOnA6wD5/uOtg4YySDFA+oTHOoDHWaLdGLAMRkOb3E5PJ8BQkDFgdB04t89/1O/w1cDnyilFU=';

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
if ($message_type == "text") {
    //返信メッセージ
    $return_message_text = $message_text . "←とはどういう意味ですか？";
} elseif ($message_type == "sticker") {
    $return_message_text = "そのスタンプかわいいね！";
    $message_type = "text";
} else {
    $return_message_text = "それいいね！？";
    $message_type = "text";
}

$post_data = [
    "type" => $message_type,
    "text" => $message_text
];


$result_data = sending_local($post_data);


//返信実行
sending_messages($accessToken, $replyToken, $message_type, $result_data);
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
    $ch = curl_init("http://localhost:9000/");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charser=UTF-8',
    ));

    //レスポンスbodyを取得
    $result = curl_exec($ch).file_get_contents('php://input');
    curl_close($ch);

    return $result;
}

?>