window.App = {
  init: function(){
	$('input[placeholder]').placeholder(); 
	this.togglePwd(); 
	this.validateSignup();
	this.submitSignupForm(); 
  },
	
  
  regex: {
  	username: /^[a-z](?=[\w.]{3,31}$)\w*\.?\w*$/i,
  	email: /[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[a-z]{2}|com|org|net|edu|gov|mil|biz|info|mobi|name|aero|asia|jobs|museum)$/,   
  	password: /(?!^[0-9]*$)(?!^[a-zA-Z!@#$%^&*()_+=<>?]*$)^([a-zA-Z!@#$%^&*()_+=<>?0-9]{6,15})$/ 
  },     
  
  togglePwd: function(){
	$("#pwdmask").prop("checked", false);
	$("#pwdmask").change(function(){
	  if ( $(this).prop("checked") ) { $("#password").prop('type', 'text'); }
	  else { $("#password").prop('type', 'password');  }
	});   
  },
  
  generateError: function(field, errMessage){
	  return $("<div/>", {
		   "class": "signup-error",
		   text: errMessage 
		}).append("<div class='triangle'></div>").clone().data("field", field); 
	
  },  
  validateHandler: function(){
	 var field = this.id, // 'this' is DOM element #name, #email or #message to be validated 
	     $this = $(this),
     	 value = $.trim( this.value ),
         regexValid, message; 
         
     switch(field){
     	case "username":
     	    regexValid = App.regex.username.test( value );
			if (!regexValid) { message = "username must be 4 to 32 characters and can contain only letters, numbers, underscores, and one dot"; messageHandle(); }
			else { 
					$.post('/signup/check_username_exist', {"username": value}, function(data) {
		   			     if ( data == 1 ) { 
                            regexValid = false;
		   				 	message = "The username you are trying to use is taken. Please Pick something else."; 
		   					messageHandle();
		   				} else { messageHandle(); }
		   			});
			} 
	    break;
	    
	    case "email":
		    regexValid = App.regex.email.test( value ); 
		    if (!regexValid)
		    {
				message = "invalid email address. An valid example would be whoiam@example.com."; 
				messageHandle();
			} else {
			 	   $.post('/signup/check_email_exist', {"email": value}, function(data) {
	   			     if ( data == 1 ) { 
                        regexValid = false;
	   				 	message = "We have your email in our database. You are probably a user here already. Go ahead and log in."; 
	   					messageHandle();
	   			     } else { messageHandle(); }
	   			   });
			}
		break;
		
		case "password":
		    regexValid = App.regex.password.test( value );
			message = "Pasword must contain 6 to 15 characters with least a number, a letter, and optionally special characters";
			messageHandle();
		break;
     }
	
	function messageHandle(){
	
   			 if (!regexValid && !$this.data("errorSet") ) {
				    $this.siblings(".ok").remove();
					App.generateError(field, message).insertAfter($this);
					$this.data("okSet", false);
				    $this.data("errorSet", true);
					$this.data("valid", false);
	    
				} 	else if ( regexValid && !$this.data("okSet") ) {

						$this.data("errorSet", false);
						$this.data("valid", true);   	
						$this.next(".signup-error").remove(); 
						$("<img src='/img/ok.png' alt='ok' class='ok'/>").insertAfter($this).css({
							position:"absolute",
							left:"300px",
							top: "-7px",
						});
						$this.data("okSet", true);      
					}                       
	 }
	
	
  },
  validateSignup: function(){
	$("#username, #password, #email", "#signup").keyup( App.validateHandler );
	$("#username, #password, #email", "#signup").blur( App.validateHandler );
  },
  submitSignupForm: function(){
	$("#signup-bt").click(function(e){
		
	   e.preventDefault();
	   $("#username, #password, #email", "#signup").each(App.validateHandler); 
	   if ( $("#username", "#signup").data('valid') && $("#email", "#signup").data('valid') && $("#password", "#signup").data('valid') )
	   {
			$("#signup").submit();
	   }
	});
  }  

  
	
};  


(function($) {      
	App.init();
})(jQuery);

 

