import HeaderLinks from "./mat-kit/Header/HeaderLinks";
import Header from "./mat-kit/Header/Header";

const dashboardRoutes = [];

export default function AslHeader() {
  return (
    <Header
      color="transparent"
      routes={dashboardRoutes}
      brand="ASL-LEARN.COM"
      rightLinks={<HeaderLinks />}
      fixed
      changeColorOnScroll={{
        height: 400,
        color: "white",
      }}
    />
  );
}
