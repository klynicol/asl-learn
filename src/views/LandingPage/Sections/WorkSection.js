import React, { useState, useContext } from "react";
// @material-ui/core components
import { makeStyles } from "@material-ui/core/styles";

// @material-ui/icons

// core components
import GridContainer from "components/mat-kit/Grid/GridContainer.js";
import GridItem from "components/mat-kit/Grid/GridItem.js";
import CustomInput from "components/mat-kit/CustomInput/CustomInput.js";
import Button from "components/mat-kit/CustomButtons/Button.js";

//Application components
import Connection from "services/Connection";
import Utility from "services/Utility";
import { ModalContext } from "services/Modal/modal-context";

import styles from "assets/mat-kit/jss/material-kit-react/views/landingPageSections/workStyle.js";

const useStyles = makeStyles(styles);

export default function WorkSection() {
  const classes = useStyles();

  const { modal, handleModal } = useContext(ModalContext);

  const [mailingListInput, setmailingListInput] = useState("");
  const [mailingListInputError, setmailingListInputError] = useState({
    isError: false,
    errorMsg: "",
  });

  const [contactFormInput, setcontactFormInput] = useState({
    name: "",
    nameError: false,
    nameErrorMsg: "",
    email: "",
    emailError: false,
    emailErrorMsg: "",
    message: "",
    messageError: false,
    messageErrorMsg: "",
  });

  const mailingListChangeHandler = (event) => {
    setmailingListInput(event.target.value);
  };

  const joinMailingListHandler = () => {
    if (!Utility.validateEmail(mailingListInput)) {
      setmailingListInputError({
        isError: true,
        errorMsg: "Email is not valid",
      });
      return;
    }
    const con = new Connection();
    con
      .sendGet(
        process.env.REACT_APP_SERVER_BASE_URL_LIVE +
          "simptasks/joinmailinglist?email=" +
          mailingListInput
      )
      .then((result) => {
        console.log(result);
        if (!result.status) {
          handleModal({
            content: "Something went wrong, please try again later"
          });
        } else {
          handleModal({
            content: "Than you for joining!"
          });
        }
      });
  };

  /**
   * little helper method to set contactformInput
   * @param {*} state
   */
  const scfHelper = (state) => {
    setcontactFormInput((prev) => {
      return { ...prev, ...state };
    });
  };

  const contractFormChangeHandler = (event) => {
    scfHelper({ [event.target.name]: event.target.value });
  };

  const contactFormSubmitHandler = (event) => {
    event.preventDefault();

    setcontactFormInput((prev) => {
      Utility.trimObjectStrings(prev);
      return prev;
    });

    let hasErrors = false;

    if (!Utility.validateEmail(contactFormInput.email)) {
      scfHelper({ emailError: true, emailErrorMsg: "Email is not valid" });
      hasErrors = true;
    } else if (contactFormInput.email.length > 320) {
      scfHelper({ emailError: true, emailErrorMsg: "Email is too long" });
      hasErrors = true;
    }

    if (contactFormInput.name.length > 320) {
      scfHelper({
        nameError: true,
        nameErrorMsg: "Name must be shorter than 320 characters",
      });
      hasErrors = true;
    }

    if (contactFormInput.message.length > 184000) {
      scfHelper({
        messageError: true,
        messageErrorMsg: "Message is too long, please shorten",
      });
      hasErrors = true;
    }

    if (hasErrors) {
      return;
    }

    const con = new Connection();
    con
      .sendPost(
        {
          name: contactFormInput.name,
          email: contactFormInput.email,
          message: contactFormInput.message,
        },
        process.env.REACT_APP_SERVER_BASE_URL_LIVE + "simptasks/contactusform"
      )
      .then((result) => {
        console.log(result);
        if (!result.status) {
          handleModal({
            content: result.msg
          });
        } else {
          handleModal({
            content: "Thank you for contacting us, we'll be in touch"
          });
        }
      });
  };

  return (
    <div className={classes.section}>
      <GridContainer justify="center">
        <GridItem
          cs={12}
          sm={12}
          md={8}
          style={{
            background: "white",
            paddingBottom: "34px",
            borderRadius: "4px",
          }}
        >
          <h2 className={classes.title}>Join Mailing List</h2>
          <form>
            <GridContainer>
              <GridItem xs={12} sm={12} md={8} style={{ textAlign: "center" }}>
                <CustomInput
                  labelText="Your Email"
                  id="mail_email"
                  errorHelper={mailingListInputError.errorMsg}
                  error={mailingListInputError.isError}
                  inputProps={{
                    onChange: mailingListChangeHandler,
                  }}
                  formControlProps={{
                    fullWidth: true,
                  }}
                />
              </GridItem>
              <GridItem xs={12} sm={12} md={4} style={{ textAlign: "center" }}>
                <Button color="rose" onClick={joinMailingListHandler}>
                  Join
                </Button>
              </GridItem>
            </GridContainer>
          </form>
        </GridItem>
      </GridContainer>
      <div style={{ height: "20px" }}></div>
      <GridContainer justify="center">
        <GridItem
          cs={12}
          sm={12}
          md={8}
          style={{
            background: "white",
            paddingBottom: "34px",
            borderRadius: "4px",
          }}
        >
          <h2 className={classes.title}>Contact us</h2>
          <h4 className={classes.description}>
            What do you want to learn?
          </h4>
          <form onSubmit={contactFormSubmitHandler}>
            <GridContainer>
              <GridItem xs={12} sm={12} md={6}>
                <CustomInput
                  labelText="Your Name"
                  id="name"
                  error={contactFormInput.nameError}
                  errorHelper={contactFormInput.nameErrorMsg}
                  inputProps={{
                    onChange: contractFormChangeHandler,
                    name: "name",
                  }}
                  formControlProps={{
                    fullWidth: true,
                  }}
                />
              </GridItem>
              <GridItem xs={12} sm={12} md={6}>
                <CustomInput
                  labelText="Your Email"
                  id="email"
                  error={contactFormInput.emailError}
                  errorHelper={contactFormInput.emailErrorMsg}
                  inputProps={{
                    onChange: contractFormChangeHandler,
                    name: "email",
                  }}
                  formControlProps={{
                    fullWidth: true,
                  }}
                />
              </GridItem>
              <CustomInput
                labelText="Your Message"
                id="message"
                error={contactFormInput.messageError}
                errorHelper={contactFormInput.messageErrorMsg}
                formControlProps={{
                  fullWidth: true,
                  className: classes.textArea,
                }}
                inputProps={{
                  onChange: contractFormChangeHandler,
                  multiline: true,
                  rows: 5,
                  name: "message",
                }}
              />
              <GridItem xs={12} sm={12} md={4}>
                <Button color="primary" type="submit">
                  Send Message
                </Button>
              </GridItem>
            </GridContainer>
          </form>
        </GridItem>
      </GridContainer>
    </div>
  );
}
