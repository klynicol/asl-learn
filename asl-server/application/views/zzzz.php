<?php
defined('BASEPATH') OR exit('No direct script access allowed');

echo "<h1>Forgot Password</h1>";
echo form_open('https://asl-learn.com/asl-server/index.php/bang/frgpassone');
echo form_input(['name' => 'email', 'placeholder' => "Email"]);
echo form_submit('submit', 'Send Reset', "id='submit'");
echo form_close();
?>

<script>
document.addEventListener('DOMContentLoaded', function(event) {
    document.getElementById('submit').addEventListener('click', function(event){
        event.preventDefault();
        var form = document.querySelector('form');
        var formData = new FormData(form);
        let con = new Connection();
        con.sendPost(formData, form.action)
        .then(result => {
            if(!result.status){
                window.alert(result.message);
            }
            console.log(result);
        })
    })
});

</script>

<script>

class Connection {
    objectToFormData(obj, form, namespace) {
      var fd = form || new FormData();
      var formKey;
  
      for (var property in obj) {
        if (obj.hasOwnProperty(property)) {
          if (namespace) {
            formKey = namespace + "[" + property + "]";
          } else {
            formKey = property;
          }
          // if the property is an object, but not a File,
          // use recursivity.
          if (
            typeof obj[property] === "object" &&
            !(obj[property] instanceof File)
          ) {
            this.objectToFormData(obj[property], fd, property);
          } else {
            // if it's a string or a File object
            fd.append(formKey, obj[property]);
          }
        }
      }
      return fd;
    }
  
    async sendGet(url, alert = false) {
      const response = await fetch(url, {
        method: "GET",
        cache: "no-cache"
      })
        .then((response) => {
          this.lastRespone = response;
          const contentType = response.headers.get("content-type");
          let data;
  
          //Attempt to parse the data
          if (contentType.includes("application/json")) {
            data = response.json();
          } else if (contentType.includes("text/html")) {
            data = response.text();
          } else {
            data = response.blob();
          }
  
          if (!response.ok) {
            // get error message from body or default to response status
            const error = data.message || data || response.status;
            return Promise.reject(error);
          }
          return data;
        })
        .catch((err) => {
          console.log(err);
          if (alert) alert(err);
        });
      return response;
    }
  
    /**
     * Wrapper for fetch that will detect data type and attempt to parse.
     * Errors are logged and alerted by default.
     *
     * @param {*} input JSON parsable info
     * @param {*} url
     */
    async sendPost(input, url, alert = true) {
      const result = await fetch(url, {
        method: "POST",
        mode: "cors",
        cache: "no-cache",
        body: input
      })
        .then((response) => {
          this.lastRespone = response;
          const contentType = response.headers.get("content-type");
          let data;
  
          //Attempt to parse the data
          if (contentType.includes("application/json")) {
            data = response.json();
          } else if (contentType.includes("text/html")) {
            data = response.text();
          } else {
            data = response.blob();
          }
  
          if (!response.ok) {
            // get error message from body or default to response status
            const error = data.message || data || response.status;
            return Promise.reject(error);
          }
          return data;
        })
        .catch((err) => {
          console.log(err);
          if (alert) alert(err);
        });
      return result;
    }

    
  }
  
</script>