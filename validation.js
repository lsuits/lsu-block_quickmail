function block_quickmail_testInfo(email){
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;  
    var OK = re.exec(email);
    if (!OK)  
        return false;  
    else
      return true;
}  



function block_quickmail_mycallback(input){
    var thebool = true;
    input = input.split(/[\s]*,[\s]*|[\s]*;[\s]*/);
    var i;
    for(i = 0; i < input.length;i++){
        if(block_quickmail_testInfo(input[i]) == false){
            return false;
        }
        else{
            thebool = true;
        }
    }
	if(thebool){
		return true;
	}
	else{
		return false;
	}
}
    
