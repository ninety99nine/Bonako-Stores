<template>

  <Row :gutter="12" class="mt-5">
    <Col :span="20" :offset="2">
      <Row :gutter="12">
        <Col :span="8">
        
          <!-- Add Location Button -->
          <Card class="add-bos-mini-card-button mb-3" @click.native="navigateToCreateLocation()">
            <div class="action-title">
              <Icon type="ios-add" />
              <span>Add Location</span>
            </div>
          </Card>

          <singleLocationCard 
              v-for="(location, index) in firstColumnLocations"
              :key="index" :index="index" :store="store" :location="location">
          </singleLocationCard>

        </Col>

        <Col :span="8">
          <singleLocationCard
            v-for="(location, index) in secondColumnLocations"
            :key="index"
            :index="index"
            :store="store"
            :location="location">
          </singleLocationCard>
        </Col>

        <Col :span="8">
          <singleLocationCard
            v-for="(location, index) in thirdColumnLocations"
            :key="index"
            :index="index"
            :store="store"
            :location="location">
          </singleLocationCard>
        </Col>
      </Row>
    </Col>
  </Row>
</template>

<script>
import singleLocationCard from "./components/singleLocationCard.vue";

export default {
  props: {
    store: {
      type: Object,
      default: null
    }
  },
  components: { singleLocationCard },
  data() {
    return {
      locations: []
    };
  },
  computed: {
    storeUrl() {
      return this.store["_links"]["self"].href;
    },
    locationsUrl() {
      return this.store["_links"]["bos:locations"].href;
    },
    firstColumnLocations() {
      return this.locations.filter((location, index) => {
        var position = index + 1;
        if (position == 3 || position % 3 == 0) {
          return true;
        }
      });
    },
    secondColumnLocations() {
      return this.locations.filter((location, index) => {
        var position = index + 1;
        if (position == 1 || position % 3 == 1) {
          return true;
        }
      });
    },
    thirdColumnLocations() {
      return this.locations.filter((location, index) => {
        var position = index + 1;
        if (position == 2 || position % 3 == 2) {
          return true;
        }
      });
    }
  },
  methods: {
    navigateToCreateLocation() {
      //  Navigate to create new location
      this.$router.push({
        name: "create-location",
        param: { store_url: this.storeUrl }
      });
    },
    fetchLocations() {
      //  If we have the location url
      if (this.locationsUrl) {
        //  Hold constant reference to the current Vue instance
        const self = this;

        //  Start loader
        self.isLoading = true;

        //  Use the api call() function, refer to api.js
        api
          .call("get", this.locationsUrl)
          .then(({ data }) => {
            //  Get the locations
            self.locations = ((data || [])["_embedded"] || [])["locations"];

            //  Stop loader
            self.isLoading = false;
          })
          .catch(response => {
            //  Log the responce
            console.error(response);

            //  Stop loader
            self.isLoading = false;
          });
      }
    }
  },
  created() {
    //  Fetch the location
    this.fetchLocations();
  }
};
</script>
