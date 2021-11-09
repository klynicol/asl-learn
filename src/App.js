import React from "react";
import { createBrowserHistory } from "history";
import { Route, Switch, Redirect, BrowserRouter } from "react-router-dom";
import { UserCtxProvider } from "state/user-context";
import { ModalProvider } from "services/Modal/modal-context";

// pages for this product
import LandingPage from "views/LandingPage/LandingPage";
import Dashboard from "views/Dashboard/Dashboard";
import Login from "views/Login/Login";
import ClassesSchedule from "views/Classes/Schedule/ClassesSchedule";

import PrivateRoute from "services/PrivateRoute";

var hist = createBrowserHistory();

const App = () => {
  
  return (
    <UserCtxProvider>
      <ModalProvider>
      <BrowserRouter history={hist}>
        <Switch>
          <PrivateRoute path="/dashboard" component={Dashboard} exact />
          <Route path="/classes/about" component={Login} exact />
          <Route path="/classes/schedule" component={ClassesSchedule} exact />
          <Route path="/login" component={Login} exact />
          <Route path="/" component={LandingPage} />
        </Switch>
      </BrowserRouter>
      </ModalProvider>
    </UserCtxProvider>
  );
};

export default App;
