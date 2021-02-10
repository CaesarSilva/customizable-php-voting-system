# customizable-php-voting-system

Simple voting system that uses PHP  .
It currently only supports one type of question.  
For testing, it supports a testing hardcoded account, that can be enabled or disabled on config.php  
This testing account can be allowed to vote multiple times, it can be disabled on config.php, this is useful for testing the voting system.
It probably needs some fixes before it can be used on a real project.  
One of the main issues is that in order to add a new type of question, multiple files need to be edited. My plan is to include all these shared functions on shared.php, and organize the code in order to make it easy to add new types of questions.

 
 https://code.caesarsilva.xyz/vote/results.php?id=1 (results)
 https://code.caesarsilva.xyz/vote/ (1@0000 for test account)  
 
 
 
  # discord bot
  
 It detects when a player says !vote on a server and sends the user an invitation privately.  
 If the discord user has already received an invitation, nothing happens.  
 It depends on discord.js, jssha, dotenv. They can be installed using npm install.  
 The simple php api is located in getinvitation.php.
 The bot can only handle one votation at a time, the votation ID can be edited on .env.
 
 
 
