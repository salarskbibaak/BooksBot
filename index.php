<?php
  require 'vendor/autoload.php'; //Подключаем библиотеку;
  use Telegram\Bot\Api; 

  $telegram = new Api('680225339:AAFoHWnPG5KVG_9lD8IrbbBhqDmhYxtKyKE'); //Устанавливаем токен, полученный у BotFather
  $result = $telegram -> getWebhookUpdates(); //Передаем в переменную $result полную информацию о сообщении пользовате
  $text = $result["message"]["text"]; //Текст сообщения
  $chat_id = $result["message"]["chat"]["id"]; //Уникальный идентификатор пользователя
  $name = $result["message"]["from"]["username"]; //Юзернейм пользователя
  $keyboard = [["My library"], ["Search book"], ["Say Hello"]]; //Клавиатура

  if ($text)
  {
    if ($text == "Say Hello" or $text == "/start") {
      if ($name != "")
      {
        $reply = "Hello, ". $name . "!";
      }
    
      else
      {
        $reply = "Hello, stranger!";
      }
      $reply_markup = $telegram->replyKeyboardMarkup([ 'keyboard' => $keyboard, 'resize_keyboard' => true, 'one_time_keyboard' => false ]);
      $telegram->sendMessage([ 'chat_id' => $chat_id, 'text' => $reply, 'reply_markup' => $reply_markup ]);
    }
  }
  
?>