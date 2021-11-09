import GridContainer from "components/mat-kit/Grid/GridContainer";
import GridItem from "components/mat-kit/Grid/GridItem";
import CustomInput from "components/mat-kit/CustomInput/CustomInput";
import Button from "component/mat-kit/CustomButtons/Button";

export default function SignupForm() {
  return (
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
  );
}
