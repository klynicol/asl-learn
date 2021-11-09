import React, { useState, useEffect, useContext } from "react";
import GridContainer from "components/mat-kit/Grid/GridContainer";
import CustomTabs from "components/mat-kit/CustomTabs/CustomTabs";
import Button from "components/mat-kit/CustomButtons/Button";
import Utility from "services/Utility";
import DescriptionIcon from "@material-ui/icons/Description";
import VpnKeyIcon from "@material-ui/icons/VpnKey";
import Paypal from "services/Paypal";

import { Grid } from "@material-ui/core";
import { TextField } from "@material-ui/core";
import CustomInput from "components/mat-kit/CustomInput/CustomInput";
import { ModalContext } from "services/Modal/modal-context";
import Connection from "services/Connection";

const classes = {
  textCenter: {
    textAlign: "center",
  },
};

const con = new Connection();

export default function MeetingPublicView(props) {
  const { meeting } = props;

  const [selectedTab, setSelectedTab] = useState(0);

  const [showSignupButton, setShowSignupButton] = useState(false);
  const { modal, handleModal } = useContext(ModalContext);

  const [formData, setFormData] = useState({
    first_name: "",
    first_name_error: false,
    first_name_error_msg: "",
    last_name: "",
    last_name_error: false,
    last_name_error_msg: "",
    email: "",
    email_error: false,
    email_error_msg: "",
    message: "",
  });

  useEffect(() => {
    if (
      formData.first_name.length > 2 &&
      formData.last_name.length > 2 &&
      isValidEmail(formData.email)
    ) {
      setShowSignupButton(true);
      clearError("first_name");
      clearError("last_name");
      clearError("email");
    } else {
      setShowSignupButton(false);
    }
  }, [formData.first_name, formData.last_name, formData.email]);

  function setFormDataHelper(data) {
    setFormData((prev) => {
      return { ...prev, ...data };
    });
  }

  const handleChangeTab = (value) => {
    setSelectedTab(value);
  };

  function setError(field, message) {
    setFormDataHelper({
      [`${field}_error`]: true,
      [`${field}_error_msg`]: message,
    });
  }

  function clearError(field) {
    setFormDataHelper({
      [`${field}_error`]: false,
      [`${field}_error_msg`]: "",
    });
  }

  function firstNameOnBlur() {
    if (formData.first_name.length === 0) {
      setError("first_name", "First Name is required");
    } else if (formData.first_name.length <= 2) {
      setError("first_name", "First Name must be two characters or longer");
    } else {
      clearError("first_name");
    }
  }

  function lastNameOnBlur() {
    if (formData.last_name.length === 0) {
      setError("last_name", "Last Name is required");
    } else if (formData.last_name.length <= 2) {
      setError("last_name", "Last Name must be two characters or longer");
    } else {
      clearError("last_name");
    }
  }

  function emailOnBlur() {
    if (!isValidEmail(formData.email)) {
      setError("email", "Not valid email");
    }
  }

  function isValidEmail(email) {
    return email.match(/[^@ \t\r\n]+@[^@ \t\r\n]+\.[^@ \t\r\n]+/) !== null;
  }

  function parseMeetingDateTime(dateTime) {
    return (
      <span style={{ fontSize: 20 }}>{Utility.timeStringFull(dateTime)}</span>
    );
  }

  function onPaypalApprove() {
    con.sendPost(process.env.REACT_APP_SERVER_BASE_URL_LIVE + '/guest/meeting/signup', {
      meeting_id: meeting.id,
      first_name: formData.first_name,
      last_name: formData.last_name,
      email: formData.email,
      message: formData.message,
      amount_paid: meeting.cost
    })
    handleModal({
      content:
        "Thank you for registering, an email will be sent confirming your signup and next actions",
    });
  }

  return (
    <GridContainer>
      <CustomTabs
        headerColor="info"
        title={parseMeetingDateTime(meeting.date_time)}
        parentManagedValue={selectedTab}
        parentHandleChange={handleChangeTab}
        rtlActive={true}
        tabs={[
          {
            tabName: "Overview",
            tabIcon: DescriptionIcon,
            tabContent: (
              <React.Fragment>
                <h4>
                  {meeting.email} {meeting.first_name} {meeting.last_name}
                </h4>
                <p className={classes.textCenter}>{meeting.description}</p>
                <p style={{ fontWeight: 800 }}>
                  Registration cost ${meeting.cost}
                </p>
                <Button
                  onClick={() => {
                    setSelectedTab(1);
                  }}
                >
                  Register Now!
                </Button>
              </React.Fragment>
            ),
          },
          {
            tabName: "Register",
            tabIcon: VpnKeyIcon,
            tabContent: (
              <div>
                <GridContainer spacing={1}>
                  {/** FIRST NAME */}
                  <Grid item xs={12} md={4}>
                    <CustomInput
                      labelText="* First Name"
                      id="first-name"
                      error={formData.first_name_error}
                      errorHelper={formData.first_name_error_msg}
                      formControlProps={{
                        fullWidth: true,
                      }}
                      inputProps={{
                        onChange: (event) =>
                          setFormDataHelper({
                            first_name: event.target.value,
                          }),
                        onBlur: firstNameOnBlur,
                        name: "first-name",
                        value: formData.first_name,
                      }}
                    />
                  </Grid>
                  {/** LAST NAME */}
                  <Grid item xs={12} md={4}>
                    <CustomInput
                      labelText="* Last Name"
                      id="last-name"
                      error={formData.last_name_error}
                      errorHelper={formData.last_name_error_msg}
                      formControlProps={{
                        fullWidth: true,
                      }}
                      inputProps={{
                        onChange: (event) =>
                          setFormDataHelper({
                            last_name: event.target.value,
                          }),
                        onBlur: lastNameOnBlur,
                        name: "last-name",
                        value: formData.last_name,
                      }}
                    />
                    {/** EMAIL */}
                  </Grid>
                  <Grid item xs={12} md={4}>
                    <CustomInput
                      labelText="* Email"
                      id="email"
                      error={formData.email_error}
                      errorHelper={formData.email_error_msg}
                      formControlProps={{
                        fullWidth: true,
                      }}
                      inputProps={{
                        onChange: (event) =>
                          setFormDataHelper({ email: event.target.value }),
                        onBlur: emailOnBlur,
                        name: "email",
                        value: formData.email,
                      }}
                    />
                  </Grid>
                  <Grid item xs={12}>
                    {/** MESSAGE */}
                    <CustomInput
                      labelText="Additional Instructions"
                      id="message"
                      inputProps={{
                        onChange: (event) =>
                          setFormDataHelper({ message: event.target.value }),
                        name: "message",
                        value: formData.message,
                        multiline: true,
                      }}
                      formControlProps={{
                        fullWidth: true,
                      }}
                    />
                  </Grid>
                </GridContainer>
                <GridContainer>
                  <Grid item>
                    <h5>Registration Cost ${meeting.cost}</h5>
                  </Grid>
                </GridContainer>
                {showSignupButton && (
                  <Paypal amount={meeting.cost} onApprove={onPaypalApprove} />
                )}
                ,
              </div>
            ),
          },
        ]}
      />
    </GridContainer>
  );
}
