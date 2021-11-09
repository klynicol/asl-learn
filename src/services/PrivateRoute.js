import { useContext } from 'react';
import { Route, Redirect } from 'react-router-dom';
import UserCtx from 'state/user-context';

export default function PrivateRoute({ component: Component, ...rest }) {
    const userCtx = useContext(UserCtx);
  return (
    <Route
      {...rest}
      render={({ location }) => {
        console.log('something');
        return userCtx.userToken !== false ? (
          <Component />
        ) : (
          <Redirect to={{ pathname: "/login", state: { from: location } }} />
        );
      }}
    />
  );
}
