<template>
  <div>
    <k-view>
      <div class="ms-header">
        <div class="k-header-tabs">
          <nav>
            <k-button
              v-for="tab in tabs"
              :key="tab.component"
              :current="currentTab === tab.component"
              :icon="tab.icon"
              class="k-tab-button"
              @click="currentTab = tab.component"
            >
              {{ tab.component }}
            </k-button>
          </nav>
        </div>
        <Crumbs v-if="crumbs.length" v-model="crumbs"></Crumbs>
      </div>

      <component :is="currentTab"></component>
    </k-view>

    <transition name="slide">
      <div v-if="alerts.length" class="ms-alerts-wrapper">
        <div class="ms-alerts-pad">
          <div class="ms-alerts">
            <k-box
              v-for="(alert, index) in alerts"
              :key="index"
              :text="alert.text"
              :theme="alert.type"
            />

            <k-button @click="$store.commit('CLEAR_ALERTS')" icon="check">
              Close
            </k-button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import whenExpired from 'when-expired'
import Crumbs from './components/Crumbs.vue'
import Service from './components/tabs/service/Service.vue'
import User from './components/tabs/user/User.vue'
import createStore from './store'

export default {
  components: {
    Crumbs,
    Service,
    User
  },
  data () {
    return {
      tabs: [
        {
          icon: 'page',
          component: 'Service'
        },
        {
          icon: 'user',
          component: 'User'
        }
      ]
    }
  },
  computed: {
    currentTab: {
      get () {
        return this.$store.state.tab
      },
      set (value) {
        return this.$store.commit('TAB', value)
      }
    },
    crumbs: {
      get () {
        return this.$store.state.crumbs
      },
      set (value) {
        return this.$store.commit('CRUMBS', value)
      }
    },
    alerts () {
      return this.$store.state.alerts
    }
  },
  beforeCreate () {
    var Vuex = this.$root.constructor._installedPlugins.find(entry => !!entry.Store)
    this.$store = createStore(Vuex, this.$root.$store)
  },
  created () {
    if (this.$store.state.session) {
      this.currentTab = 'Service'
    } else {
      this.currentTab = 'User'
    }
  },
  watch: {
    "$store.state.session.expires": {
      immediate: true,
      handler: function (value) {
        if (value) {
          whenExpired('session', value).then(() => {
            this.$store.dispatch('logOut')
            this.$store.commit('ALERT', {
              text: 'Your session expired, please log in again.'
            })
          })
        }
      }
    }
  }
}
</script>

<style lang="scss" scoped>
$easeOutCubic: cubic-bezier(0.215, 0.61, 0.355, 1);

.ms-header {
  margin-top: 3rem;
  margin-bottom: 2rem;
}

  nav {
    border-bottom: 1px solid #ccc;
  }

    .k-tab-button[aria-current]:after {
      display: none;
    }

  /deep/ .ms-crumbs {
    border-top: none;
  }

/deep/ {
  .k-view {
  max-width: 50rem;
  }

  .ms-button {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 1.5rem;
    border-radius: 2px;

    background: #16171a;
    color: white;

    &.ms-t1 {
      background: #4271ae;
    }

    &.ms-t2 {
      background: #5d800d;
    }
  }

  .ms-actions {
    display: flex;
    align-items: center;
    justify-content: center;

    button {
      min-width: 10em;

      & + button {
        margin-left: 2rem;
      }
    }
  }
}


.ms-alerts-wrapper {
  width: 100%;
  padding-top: 6px; // for shadow
  position: fixed;
    bottom: 0;
    left: 0; 
  transition: all 0.3s ease;
  overflow: hidden;

  &/deep/ {
    .k-button {
      display: block;
      margin: 1rem auto;
    }
  }
}

  .ms-alerts-pad {
    padding: 0.1px 0;
    background: #f6f6f6;
    box-shadow: 0 0 5px 0 rgba(#000, 0.1);
    transition: transform 0.3s $easeOutCubic;
  }

  .ms-alerts {
    max-width: 25em;
    margin: 2rem auto;

    &/deep/ {
      .k-box {
        margin-bottom: 0.5rem;
      }
    }
  }

.slide-enter,
.slide-leave-to {
  .ms-alerts-pad {
    transform: translateY(100%);
  }
}
</style>
