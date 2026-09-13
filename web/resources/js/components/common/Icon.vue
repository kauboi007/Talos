<script setup lang="ts">
import { computed } from 'vue';
import {
  LayoutDashboard,
  Users,
  Route,
  Database,
  Bolt,
  Shield,
  MapPin,
  Map as MapIcon,
  HelpCircle,
  Settings,
  Lock,
  Menu,
  X,
  Search,
  Bell,
  AlertTriangle,
  Compass,
  Award,
  Radio,
  Server,
  Network,
  ArrowLeftRight,
  Download,
  ChevronLeft,
  ChevronRight,
  Filter,
  LogIn,
  Home,
  CheckCircle2,
  ArrowRight,
  GitFork,
  FileText,
  BadgeAlert,
  KeyRound,
  Crosshair,
  Navigation,
  Cpu,
  Star,
  User,
  HardDrive
} from 'lucide-vue-next';

interface Props {
  name: string;
  size?: number | string;
  class?: string;
}

const props = withDefaults(defineProps<Props>(), {
  size: 18,
  class: ''
});

// Map common material symbols names to Lucide icons
const iconMap: Record<string, any> = {
  // Navigation & Core
  home: Home,
  dashboard: LayoutDashboard,
  groups: Users,
  group: Users,
  route: Route,
  alt_route: GitFork,
  database: Database,
  bolt: Bolt,
  shield: Shield,
  security: Shield,
  map: MapIcon,
  settings: Settings,
  help: HelpCircle,
  lock: Lock,
  menu: Menu,
  close: X,
  search: Search,
  notifications: Bell,
  warning: AlertTriangle,
  explore: Compass,
  military_tech: Award,
  radar: Radio,
  dns: Server,
  hub: Network,
  swap_horiz: ArrowLeftRight,
  file_download: Download,
  download: Download,
  chevron_left: ChevronLeft,
  chevron_right: ChevronRight,
  filter_list: Filter,
  login: LogIn,
  check: CheckCircle2,
  arrow_forward: ArrowRight,
  description: FileText,
  badge: BadgeAlert,
  key: KeyRound,
  my_location: Crosshair,
  location_on: MapPin,
  pin: MapPin,
  navigate: Navigation,
  memory: Cpu,
  storage: HardDrive,
  star: Star,
  person: User,
  user: User
};

const resolvedIcon = computed(() => {
  const cleanName = props.name.trim().toLowerCase();
  return iconMap[cleanName] || null;
});

const numericSize = computed(() => {
  if (typeof props.size === 'number') return props.size;
  const parsed = parseInt(props.size, 10);
  return isNaN(parsed) ? 18 : parsed;
});
</script>

<template>
  <component
    v-if="resolvedIcon"
    :is="resolvedIcon"
    :size="numericSize"
    :class="['inline-block shrink-0 align-middle', props.class]"
  />
  <span
    v-else
    :class="['material-symbols-outlined align-middle inline-block', props.class]"
    :style="{ fontSize: `${numericSize}px` }"
  >
    {{ props.name }}
  </span>
</template>
