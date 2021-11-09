import React, { useState, useEffect, useMemo } from "react";

const UserCtx = React.createContext();
export default UserCtx; 

/**
     * TODO, loading the key from local storage should be more portable.
     * Should make a custom route that handles doing that wherever
     * we should need it.
     */
 console.log("User Provider Use Effect");
 var foundUserToken = false;
 // Load from local storage on reload
 if(localStorage.getItem('asl-key')){
   //Todo load user??
   foundUserToken = localStorage.getItem('asl-key');
 }

export const UserCtxProvider = (props) =>  {

  
  const [userToken, setUserToken] = useState(foundUserToken);
  const [user, setUser] = useState(false);

  function setUserTokenHelper(token){
    setUserToken(token);
    localStorage.setItem('asl-key', token); 
  }

  return (
    <UserCtx.Provider
      value={{
        userToken: userToken,
        setUserToken: setUserTokenHelper,
        user: user,
        setUser: setUser
      }}
    >
      {props.children}
    </UserCtx.Provider>
  );
}
