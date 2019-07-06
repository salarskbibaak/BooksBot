<?php
  require 'vendor/autoload.php';
  require_once('database.php');
  require_once('telegramAPI.php');

  const lineBreak = "\n";
  const emptySrting = "";
  const blank = " ";
  const plus = "+";
  const welcoming = "Hello, ";
  const hello = "Hello";
  const startDialog = "/start";
  const help = "Help";
  const botOpportunities = "This bot can find books by title.\n If you want to find more accurately, you should enter title and author in the way:\nМы\nЗамятин";
  const showHistory = "Show history";
  const bookSearchWarning = "Write correct title of book";
  const emptyHistoryReply = "Your history is empty now. Let`s find books!";

  $chatId = getChatId(getTelegramData($telegram));
  $name = getUserName(getTelegramData($telegram));
  $text = getText(getTelegramData($telegram));
  $reply_markup = getReplyMarkup($keyboard, $telegram);

  if ($text) {
    if ($text == hello or $text == startDialog) {

      if ($name != emptySrting) {
        $reply = welcoming . $name . "!";
      }

      else {
        $reply = welcoming . ", stranger!";
      }
      sendNewMessage($chatId, $reply, $reply_markup, $telegram);
    }

    elseif ($text == help) {
      $reply = botOpportunities;
      sendNewMessage($chatId, $reply, $reply_markup, $telegram);
    }

    elseif ($text == showHistory)
    {
      $db = getBd();
      $db->where (userId, $chatId);
      $bookHistory = $db->getOne (bookHistoryTable);
      if (!$bookHistory) {
        $reply = emptyHistoryReply;
        sendNewMessage($chatId, $reply, $reply_markup, $telegram);
      }
      elseif (!$text) {
        $bookHistory = array_slice($bookHistory, 1);
        $reply = '';
        foreach ($bookHistory as $books) {
          if ($books != emptyField) {
            $reply = $books;
            sendNewMessage($chatId, $reply, $reply_markup, $telegram);
          }
        }
      }
    }

    else {
      if (strpos($text, lineBreak)) {
        $text = explode(lineBreak, $text);
        $reply = getResponseText($text[0], $text[1], $chatId);
        sendNewMessage($chatId, $reply, $reply_markup, $telegram);
      }
      else {
        $bookAuthor = emptySrting;
        $reply = getResponseText($text, $bookAuthor, $chatId);
        sendNewMessage($chatId, $reply, $reply_markup, $telegram);
      }
    }
  }

  function getResponseText($bookName, $bookAuthor, $chatId) { 
    $bookInfo = getBookInfo($bookName, $bookAuthor, $chatId);
    if ($bookInfo["totalItems"] == 0) {
      return bookSearchWarning;
    }
    else {
      $bookTitle = $bookInfo["items"][0]["volumeInfo"]["title"];
      $authors = $bookInfo["items"][0]["volumeInfo"]["authors"][0];
      $bookInfo = $bookInfo["items"][0]["volumeInfo"]["infoLink"];
      addBookToHistory($bookInfo, $chatId);
      return "Name of the book: " . $bookTitle ."\nAuthor: ". $authors . " \nMore information about this book: " . $bookInfo . "";
    }
  }

  function getBookInfo($bookName, $bookAuthor, $chatId): ?array { 
    $bookName = str_replace(' ', '+', $bookName);
    if ($bookAuthor == emptySrting) {
      $bookInfo = file_get_contents('https://www.googleapis.com/books/v1/volumes?q=intitle:'. $bookName .'&maxResults=1&orderBy=relevance&key=AIzaSyALM0SWc1JdHtgpPplJ6T2k9Fwcc1dI7vk');
    }
    else
    {
      $bookInfo = file_get_contents('https://www.googleapis.com/books/v1/volumes?q=intitle:'. $bookName .'+inauthor:'. $bookAuthor .'&maxResults=1&orderBy=relevance&key=AIzaSyALM0SWc1JdHtgpPplJ6T2k9Fwcc1dI7vk');
    }
    return json_decode($bookInfo, true);
  }

  function sendNewMessage($chatId, $reply, $reply_markup, $telegram) {
    $telegram->sendMessage(['chat_id' => $chatId, 'text' => $reply, 'reply_markup' => $reply_markup]);
  }