const Discord = require('discord.js');
const http = require('http');
const request = require('request');
const client = new Discord.Client();
const jsSHA = require("jssha");
const dotenv = require('dotenv');
dotenv.config();
const key = process.env.key ;
const voteURL = process.env.voteURL;
const dTOKEN = process.env.dTOKEN;
const currentVotation = process.env.CVotation;
var keyA,keyB;
if((key.length % 2) == 1){
  keyA = key.slice(0, (key.length-1)/2);
  keyB = key.slice((key.length-1)/2, key.length);
}else{
  keyA = key.slice(0, key.length/2);
  keyB = key.slice(key.length/2, key.length);
}
function IsJsonString(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}

function onVoteMsg(user){
  let obj = {
    userData:{
      id : "ds"+user.id,
      tag : user.tag,
      name : user.name,
    },
    votation: currentVotation,
    id : "ds"+user.id,
    tag : user.tag,
    name : user.name,
    type: "on!vote"
  };//TODO, remove some stuff above
  console.log("insideonvote:"+JSON.stringify(obj))
  apiRequest(JSON.stringify(obj), reqCallback, user);

}
function apiRequest( content , callback, user){

  //let content = JSON.stringify(ObjData);
  let sha_ob = new jsSHA('SHA-256', 'TEXT');
  sha_ob.setHMACKey(keyA, 'TEXT');
  sha_ob.update(content);
  let hmac = sha_ob.getHMAC('HEX');

  request.post(
      voteURL +'getinvitation.php',
      {form:{json:content , hmac:hmac}},
      function (error, response, body) {
          if (!error && response.statusCode == 200) {
              //console.log(body);
              reqCallback(body, user);
          }
      }
  );
  //reqCallback("bs", user)

}
function reqCallback(content,user)
{
  if(IsJsonString(content)){
    let contentjson = JSON.parse(content);
    if(contentjson.rcode === 0){
      console.log("rcode is 0");
      user.send("Vote invitation generated. Click on the link bellow to get registred to vote:");
      user.send(voteURL + "register.php?invid="+contentjson.invID+"&invkey="+contentjson.invkey);
    }else{
      console.log("rcode is not 0");
    }


  }else{
    user.send("error: invalid json");
    console.log(content);
  }

}

client.once('ready', () => {
	console.log('Ready!');
});

client.login(dTOKEN);

client.on('message', message => {
    // So the bot doesn't reply to iteself
    if (message.author.bot) return;

    // Check if the message starts with the `!` trigger
    if (message.content === "!vote") {
        // Get the user's message excluding the `!`

        // Reply to the user's message
        //message.author.send("hurra");
        //apiRequest("emptyness", reqCallback, message.author);
        onVoteMsg(message.author);
      //  message.reply(reversed);
    }
});
