import { container, title } from "assets/mat-kit/jss/material-kit-react.js";

const landingPageStyle = {
  container: {
    zIndex: "12",
    color: "#FFFFFF",
    ...container,
  },
  title: {
    ...title,
    display: "inline-block",
    position: "relative",
    marginTop: "30px",
    minHeight: "32px",
    color: "#FFFFFF",
    textDecoration: "none",
  },
  subtitle: {
    fontSize: "1.313rem",
    maxWidth: "500px",
    margin: "10px auto 0",
  },
  main: {
    background: "#FFFFFF",
    position: "relative",
    zIndex: "3",
  },
  twiterFeed: {
    borderRadius: "6px",
    color: "#FFFFFF",
    margin: "60px 14px 0px",
  },
  mainRaised: {
    margin: "-60px 14px 0px",
    borderRadius: "6px",
    // boxShadow:
    //   "0 16px 24px 2px rgba(0, 0, 0, 0.14), 0 6px 30px 5px rgba(0, 0, 0, 0.12), 0 8px 10px -5px rgba(0, 0, 0, 0.2)",
    background: "linear-gradient(0deg, #E5E5E5 0%, #fff 100%)"
  },
};

export default landingPageStyle;
