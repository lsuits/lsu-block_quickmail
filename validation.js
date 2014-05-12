function testInfo(phoneInput){  
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;  

    //console.log('inside testinfo');
    //console.log(phoneInput);
    var OK = re.exec(phoneInput);  
    if (!OK)  
        return false;  
    else
      return true;
}  



function mycallback(input)
{
    var thebool = true;
    
    //console.log("inside mycallback");
    
    input = input.split(/[\s]*,[\s]*|[\s]*;[\s]*/);
    
    
    //console.log("length of input" + input.length);
    var i;
    for(i = 0; i < input.length;i++){
        //console.log("index = " + i);
        if(testInfo(input[i]) == false){
            return false;
        } 
        else{
            thebool = true;
        }
    }
    
	if(thebool)
	{
            //console.log("true");
		return true;
	}
	else
	{
            console.log("heyyyy");
		return false;
	}
}
    
