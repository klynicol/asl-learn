import React, { useState, useRef, useContext, useEffect } from "react";
import { useHistory } from "react-router";
import { Link } from 'react-router-dom';
// @material-ui/core components
import { makeStyles } from "@material-ui/core/styles";
import InputAdornment from "@material-ui/core/InputAdornment";
import Icon from "@material-ui/core/Icon";
// @material-ui/icons
import Email from "@material-ui/icons/Email";
import People from "@material-ui/icons/People";
// core components
import Footer from "components/mat-kit/Footer/Footer.js";
import GridContainer from "components/mat-kit/Grid/GridContainer.js";
import GridItem from "components/mat-kit/Grid/GridItem.js";
import Button from "components/mat-kit/CustomButtons/Button.js";
import Card from "components/mat-kit/Card/Card.js";
import CardBody from "components/mat-kit/Card/CardBody.js";
import CardHeader from "components/mat-kit/Card/CardHeader.js";
import CardFooter from "components/mat-kit/Card/CardFooter.js";
import CustomInput from "components/mat-kit/CustomInput/CustomInput.js";

import styles from "assets/mat-kit/jss/material-kit-react/views/loginPage.js";

import image from "assets/mat-kit/img/bg7.jpg";

import AslHeader from "components/AslHeader";
import Connection from "services/Connection";
import UserCtx from "../../state/user-context";

const useStyles = makeStyles(styles);

export default function Login(props) {
  const [cardAnimaton, setCardAnimation] = React.useState("cardHidden");
  const userCtx = useContext(UserCtx);
  setTimeout(function () {
    setCardAnimation("");
  }, 700);
  const classes = useStyles();
  const emailInputRef = useRef(null);
  const passwordInputRef = useRef(null);
  const history = useHistory();

  const [formData, setFormData] = useState({
    email: "",
    password: "",
    error: false,
    errorMsg: "",
  });

  function setFormDataHelper(data) {
    setFormData((prev) => {
      return { ...prev, ...data };
    });
  }

  function handleLogin() {
    let con = new Connection();
    con
      .sendPost(process.env.REACT_APP_SERVER_BASE_URL_LIVE + "bang/login", {
        email: formData.email,
        password: formData.password,
      })
      .then((result) => {
        console.log(result);
        if (!result.status) {
          setFormDataHelper({ error: true, errorMsg: result.message });
        } else {
          console.log(result.data.token);
          userCtx.setUserToken(result.data.token);
          userCtx.setUser(result.data.user);
          history.push("/dashboard");
        }
      });
  }

  function handleEmailChange(event) {
    setFormDataHelper({ email: event.target.value });
  }

  function handlePasswordChange(event) {
    setFormDataHelper({ password: event.target.value });
  }

  return (
    <div>
      <AslHeader />
      <div
        className={classes.pageHeader}
        style={{
          backgroundImage: "url(" + image + ")",
          backgroundSize: "cover",
          backgroundPosition: "top center",
        }}
      >
        
        <div className={classes.container}>
          <GridContainer justify="center">
            <GridItem xs={12} sm={12} md={4}>
              <Card className={classes[cardAnimaton]}>
                <form className={classes.form}>
                  <CardHeader color="primary" className={classes.cardHeader}>
                    {/* <h4>Login</h4> */}
                    <div className={classes.socialLine}>
                      <Button
                        justIcon
                        href="#pablo"
                        target="_blank"
                        color="transparent"
                        onClick={(e) => e.preventDefault()}
                      >
                        <i className={"fab fa-twitter"} />
                      </Button>
                      <Button
                        justIcon
                        href="#pablo"
                        target="_blank"
                        color="transparent"
                        onClick={(e) => e.preventDefault()}
                      >
                        <i className={"fab fa-facebook"} />
                      </Button>
                      <Button
                        justIcon
                        href="#pablo"
                        target="_blank"
                        color="transparent"
                        onClick={(e) => e.preventDefault()}
                      >
                        <i className={"fab fa-google-plus-g"} />
                      </Button>
                    </div>
                  </CardHeader>
                  {/* <p className={classes.divider}>Or Be Classical</p> */}
                  <CardBody>
                  <Link to="/dashboard">Link</Link>
                    {/* <CustomInput
                      labelText="First Name..."
                      id="first"
                      formControlProps={{
                        fullWidth: true,
                      }}
                      inputProps={{
                        type: "text",
                        endAdornment: (
                          <InputAdornment position="end">
                            <People className={classes.inputIconsColor} />
                          </InputAdornment>
                        ),
                      }}
                    /> */}
                    <CustomInput
                      labelText="Email..."
                      id="email"
                      formControlProps={{
                        fullWidth: true,
                      }}
                      inputProps={{
                        type: "email",
                        onChange: handleEmailChange,
                        ref: emailInputRef,
                        endAdornment: (
                          <InputAdornment position="end">
                            <Email className={classes.inputIconsColor} />
                          </InputAdornment>
                        ),
                      }}
                    />
                    <CustomInput
                      labelText="Password"
                      id="pass"
                      formControlProps={{
                        fullWidth: true,
                      }}
                      errorHelper={formData.errorMsg}
                      inputProps={{
                        type: "password",
                        ref: passwordInputRef,
                        onChange: handlePasswordChange,
                        endAdornment: (
                          <InputAdornment position="end">
                            <Icon className={classes.inputIconsColor}>
                              lock_outline
                            </Icon>
                          </InputAdornment>
                        ),
                        autoComplete: "off",
                      }}
                    />
                  </CardBody>
                  <CardFooter className={classes.cardFooter}>
                    <Button
                      simple
                      color="primary"
                      size="lg"
                      onClick={handleLogin}
                    >
                      Login
                    </Button>
                  </CardFooter>
                </form>
              </Card>
            </GridItem>
          </GridContainer>
        </div>
        <Footer whiteFont />
      </div>
    </div>
  );
}
