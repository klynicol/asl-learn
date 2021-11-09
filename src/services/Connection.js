class Connection {
  buildFormData(data, formData, parentKey = "") {
    formData = formData || new FormData();
    if (
      data &&
      typeof data === "object" &&
      !(data instanceof Date) &&
      !(data instanceof File)
    ) {
      Object.keys(data).forEach((key) => {
        this.buildFormData(
          data[key],
          formData,
          parentKey ? `${parentKey}[${key}]` : key
        );
      });
    } else {
      const value = data == null ? "" : data;

      formData.append(parentKey, value);
    }
    return formData
  }
  
    async sendGet(url, headers = {}, alert = false) {
      const response = await fetch(url, {
        method: "GET",
        cache: "no-cache",
        headers: headers
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
    async sendPost(url, input, headers = {}, alert = true) {
      const result = await fetch(url, {
        method: "POST",
        mode: "cors",
        cache: "no-cache",
        body: this.buildFormData(input),
        headers: headers
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
          
          return data;
        })
        .catch((err) => {
          console.log(err);
        });
      return result;
    }
    
    userPost(uri, token, data = {}){
      let headers = { 'Authorization' : 'Bearer ' + token };
      return this.sendPost(process.env.REACT_APP_SERVER_BASE_URL_LIVE + uri, data, headers);
    }
    
    userGet(uri, token){
      let headers = { 'Authorization' : 'Bearer ' + token };
      return this.sendGet(process.env.REACT_APP_SERVER_BASE_URL_LIVE + uri, headers);
    }

    guestGet(uri){
      return this.sendGet(process.env.REACT_APP_SERVER_BASE_URL_LIVE + uri);
    }

    guestPost(uri, data){
      return this.sendPost(process.env.REACT_APP_SERVER_BASE_URL_LIVE + uri, data);
    }
    
  }
  export default Connection;
  