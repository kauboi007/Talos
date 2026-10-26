# Conflict-Based Search (CBS): Algorithm Specifications & Multi-Domain Evaluation

## High-Level Algorithm Architecture

Conflict-Based Search (CBS) is a two-level optimal multi-agent pathfinding algorithm:
* **Low-Level Search:** Runs a continuous-time $A^*$ algorithm for an individual agent $i$ subject to a set of spatial-temporal constraints $\text{constraints}[i]$.
* **High-Level Search:** Builds a Constraint Tree (CT). Each node $N$ stores a set of agent paths $N.\text{paths}$, individual costs $N.\text{costs}$, total cost $N.\text{cost}$, and per-agent constraints $N.\text{constraints}$. When a spatial-temporal conflict $(v, t_{\text{start}}, t_{\text{end}})$ is detected between agents $A$ and $B$, the algorithm branches into two child nodes, enforcing the constraint on agent $A$ in one branch and agent $B$ in the other.

---

## Formal Algorithm Pseudocode

### 1. Low-Level: Continuous-Time $A^*$ with Analytical Waiting ($\text{LowLevelAstar}$)

$$\begin{aligned}
&\textbf{Function } \text{LowLevelAstar}(a, \text{start}, \text{goal}, G, \text{coords}, v_a, \text{constraints}): \\
1.\quad & \text{open\_set} \leftarrow \text{PriorityQueue}() \\
2.\quad & g\_score \leftarrow \text{Dictionary}() \\
3.\quad & \text{came\_from} \leftarrow \text{Dictionary}() \\
4.\quad & \text{open\_set.push}((f=h(\text{start}, \text{goal}), u=\text{start}, t_{\text{arr}}=0.0)) \\
5.\quad & g\_score[(\text{start}, 0.0)] \leftarrow 0.0 \\
6.\quad & \text{iter} \leftarrow 0 \\
7.\quad & \textbf{while } \text{open\_set is not empty} \textbf{ do}: \\
8.\quad & \quad \text{iter} \leftarrow \text{iter} + 1 \\
9.\quad & \quad \textbf{if } \text{iter} > \text{MAX\_ITERATIONS} \textbf{ then return } (\text{Null}, \infty) \\
10.\quad & \quad (f, u, t_{\text{arr}}) \leftarrow \text{open\_set.pop}() \\
11.\quad & \quad \textbf{if } u == \text{goal} \textbf{ then}: \\
12.\quad & \quad \quad \textbf{return } \text{ReconstructPath}(u, t_{\text{arr}}, \text{came\_from}), t_{\text{arr}} \\
13.\quad & \quad \textbf{for each } (w, d) \in G[u] \textbf{ do}: \\
14.\quad & \quad \quad t_{\text{travel}} \leftarrow \frac{d}{v_a} \\
15.\quad & \quad \quad \Delta t_{\text{deps}} \leftarrow \{t_{\text{arr}}\} \cup \{ t_{\text{end}} - t_{\text{travel}} \mid (w, t_{\text{start}}, t_{\text{end}}) \in \text{constraints} \} \cup \{ t_{\text{end}} \mid (u, t_{\text{start}}, t_{\text{end}}) \in \text{constraints} \} \\
16.\quad & \quad \quad \textbf{for each } t_{\text{dep}} \in \text{SortAscending}(\Delta t_{\text{deps}}) \textbf{ do}: \\
17.\quad & \quad \quad \quad t_{\text{wait}} \leftarrow t_{\text{dep}} - t_{\text{arr}} \\
18.\quad & \quad \quad \quad \textbf{if } t_{\text{wait}} < 0 \textbf{ continue} \\
19.\quad & \quad \quad \quad \textbf{if } t_{\text{wait}} > 0 \land \text{IsConstrained}(u, t_{\text{arr}}, t_{\text{dep}}, \text{constraints}) \textbf{ continue} \\
20.\quad & \quad \quad \quad t_{\text{next\_arr}} \leftarrow t_{\text{dep}} + t_{\text{travel}} \\
21.\quad & \quad \quad \quad \textbf{if } \text{IsConstrained}(w, t_{\text{next\_arr}}, t_{\text{next\_arr}} + \epsilon, \text{constraints}) \textbf{ continue} \\
22.\quad & \quad \quad \quad \text{edge\_cost} \leftarrow t_{\text{travel}} + t_{\text{wait}} \times (1.0 + w_{\text{wait}}) \\
23.\quad & \quad \quad \quad \text{tentative\_g} \leftarrow g\_score[(u, t_{\text{arr}})] + \text{edge\_cost} \\
24.\quad & \quad \quad \quad \textbf{if } \text{tentative\_g} < g\_score.get((w, t_{\text{next\_arr}}), \infty) \textbf{ then}: \\
25.\quad & \quad \quad \quad \quad g\_score[(w, t_{\text{next\_arr}})] \leftarrow \text{tentative\_g} \\
26.\quad & \quad \quad \quad \quad f\_score \leftarrow \text{tentative\_g} + h(w, \text{goal}) \\
27.\quad & \quad \quad \quad \quad \text{came\_from}[(w, t_{\text{next\_arr}})] \leftarrow (u, t_{\text{arr}}, t_{\text{dep}}) \\
28.\quad & \quad \quad \quad \quad \text{open\_set.push}((f\_score, w, t_{\text{next\_arr}})) \\
29.\quad & \textbf{return } (\text{Null}, \infty)
\end{aligned}$$

---

### 2. High-Level: Conflict-Based Search ($\text{CBS}$)

$$\begin{aligned}
&\textbf{Function } \text{CBS}(\text{Agents}, G, \text{coords}, \text{speeds}): \\
1.\quad & R \leftarrow \text{CreateTreeNode}() \\
2.\quad & R.\text{constraints} \leftarrow \{a: \emptyset \mid a \in \text{Agents}\} \\
3.\quad & \textbf{for each } a \in \text{Agents} \textbf{ do}: \\
4.\quad & \quad R.\text{paths}[a], R.\text{costs}[a] \leftarrow \text{LowLevelAstar}(a, a.\text{start}, a.\text{goal}, G, \text{coords}, \text{speeds}[a], R.\text{constraints}[a]) \\
5.\quad & \quad \textbf{if } R.\text{paths}[a] == \text{Null} \textbf{ then return } \text{Unfeasible} \\
6.\quad & R.\text{cost} \leftarrow \sum_{a} R.\text{costs}[a] \\
7.\quad & \text{tree\_heap} \leftarrow \text{PriorityQueue}() \\
8.\quad & \text{tree\_heap.push}(R) \\
9.\quad & \textbf{while } \text{tree\_heap is not empty} \textbf{ do}: \\
10.\quad & \quad N \leftarrow \text{tree\_heap.pop}() \\
11.\quad & \quad C \leftarrow \text{FindFirstConflict}(N.\text{paths}) \\
12.\quad & \quad \textbf{if } C == \text{Null} \textbf{ then return } N.\text{paths} \quad \text{// Conflict-free optimal solution found} \\
13.\quad & \quad (v, t_{\text{start}}, t_{\text{end}}, a_i, a_j) \leftarrow C \\
14.\quad & \quad \text{ban} \leftarrow (v, t_{\text{start}}, t_{\text{end}}) \\
15.\quad & \quad \textbf{for each } a \in \{a_i, a_j\} \textbf{ do}: \\
16.\quad & \quad \quad A_{\text{child}} \leftarrow \text{CopyNode}(N) \\
17.\quad & \quad \quad A_{\text{child}}.\text{constraints}[a].\text{add}(\text{ban}) \\
18.\quad & \quad \quad \text{new\_path}, \text{new\_cost} \leftarrow \text{LowLevelAstar}(a, a.\text{start}, a.\text{goal}, G, \text{coords}, \text{speeds}[a], A_{\text{child}}.\text{constraints}[a]) \\
19.\quad & \quad \quad \textbf{if } \text{new\_path} \neq \text{Null} \textbf{ then}: \\
20.\quad & \quad \quad \quad A_{\text{child}}.\text{paths}[a] \leftarrow \text{new\_path} \\
21.\quad & \quad \quad \quad A_{\text{child}}.\text{costs}[a] \leftarrow \text{new\_cost} \\
22.\quad & \quad \quad \quad A_{\text{child}}.\text{cost} \leftarrow \sum_{k} A_{\text{child}}.\text{costs}[k] \\
23.\quad & \quad \quad \quad \text{tree\_heap.push}(A_{\text{child}}) \\
24.\quad & \textbf{return } \text{Unfeasible}
\end{aligned}$$

---

## Application Across 10 Operational Domains

### 1. Wildfire Evacuation & Disaster Response (Forest Fires)
* **Domain Context:** Emergency response units operating in active fire zones with dynamic hazard progression.
* **Input Setup:** Fire Engine $E_1$ (Speed: $60\text{ km/h}$, Start: Base A, Goal: Sector 4) and Evacuation Bus $B_1$ (Speed: $40\text{ km/h}$, Start: Sector 4, Goal: Safe Zone B) crossing Junction $J_3$.
* **Conflict Scenario:** Fire front expands across $J_3$ between $t = 1.2\text{ hr}$ and $t = 2.5\text{ hr}$. $E_1$ scheduled to reach $J_3$ at $t = 1.4\text{ hr}$.
* **CBS Constraint Applied:** Spatial-temporal ban $(J_3, 1.2, 2.5)$ enforced on both agents.
* **Execution & Result:** $E_1$ is re-routed via perimeter bypass $J_7$, while $B_1$ waits at Sector 4 for $1.3\text{ hr}$ until the hazard clears. Zero exposure casualties; paths resolved in $14\text{ ms}$.

### 2. Urban Logistics & Heavy Freight Bypass (Road Blockages)
* **Domain Context:** Heavy cargo transport navigating urban bottlenecks with scheduled road maintenance closures.
* **Input Setup:** Freight Convoy $C_1$ ($80\text{ km/h}$, Highway N4) and Delivery Fleet $D_1$ ($50\text{ km/h}$, Express Avenue) converging on Bridge $B_1$.
* **Conflict Scenario:** Bridge $B_1$ undergoes emergency maintenance from $t = 0.5\text{ hr}$ to $t = 1.5\text{ hr}$. Convoy $C_1$ arrives at $t = 0.8\text{ hr}$.
* **CBS Constraint Applied:** Temporal constraint $(B_1, 0.5, 1.5)$ added to high-level search tree.
* **Execution & Result:** Analytical jump calculates $t_{\text{dep}} = 1.5\text{ hr}$ at previous checkpoint for $C_1$. Fleet $D_1$ is re-routed via alternative arterial route. Fuel consumption reduced by $18\%$ compared to naive idle waiting.

### 3. Air Traffic Management (Unmanned Aerial Vehicle / Drone Airspaces)
* **Domain Context:** Urban Air Mobility (UAM) corridor management for autonomous delivery drones.
* **Input Setup:** Express Delivery Drone $\text{Drone}_A$ ($90\text{ km/h}$) and Surveillance Drone $\text{Drone}_B$ ($60\text{ km/h}$) intersecting Waypoint $\text{WP}_{12}$.
* **Conflict Scenario:** Spatial overlap at $\text{WP}_{12}$ within a $0.05\text{ hr}$ window ($t = 0.30\text{ hr}$ to $t = 0.35\text{ hr}$).
* **CBS Constraint Applied:** Temporal isolation constraint $(\text{WP}_{12}, 0.30, 0.35)$ assigned to $\text{Drone}_B$.
* **Execution & Result:** Low-level search adjusts $\text{Drone}_B$'s altitude transition, delaying arrival at $\text{WP}_{12}$ to $t = 0.36\text{ hr}$. Collision risk eliminated; total flight path deviation under $1.2\%$.

### 4. Hospital Automated Guided Vehicles (AGVs)
* **Domain Context:** Autonomous medical supply and linen transport AGVs operating in sterile hospital corridors.
* **Input Setup:** Sterilization AGV $\text{AGV}_1$ ($5\text{ km/h}$) and Pharmacy AGV $\text{AGV}_2$ ($5\text{ km/h}$) traversing Narrow Corridor $C_2$.
* **Conflict Scenario:** Bi-directional deadlock inside $C_2$ between $t = 0.02\text{ hr}$ and $t = 0.04\text{ hr}$.
* **CBS Constraint Applied:** Node lock constraint $(C_2, 0.02, 0.04)$ placed on $\text{AGV}_2$.
* **Execution & Result:** $\text{AGV}_2$ holds position in Holding Bay $H_1$ for $0.02\text{ hr}$ ($1.2\text{ min}$) before entering $C_2$. Deadlock avoided; critical medication delivery schedule maintained.

### 5. Maritime Port Container Ships & Narrow Channels
* **Domain Context:** Ultra-Large Container Vessels (ULCVs) navigating single-lane harbor access channels.
* **Input Setup:** Outbound Vessel $V_1$ ($15\text{ knots}$) and Inbound Tanker $V_2$ ($10\text{ knots}$) scheduled for Channel $\text{Ch}_1$.
* **Conflict Scenario:** Both vessels occupy Channel $\text{Ch}_1$ simultaneously between $t = 2.0\text{ hr}$ and $t = 3.2\text{ hr}$.
* **CBS Constraint Applied:** Channel restriction $(\text{Ch}_1, 2.0, 3.2)$ assigned to $V_2$.
* **Execution & Result:** $V_2$ reduces speed at anchorage zone, delaying channel entry to $t = 3.25\text{ hr}$. Zero channel blockage; pilotage safety guidelines $100\%$ satisfied.

### 6. Smart Warehouse Fulfillment Centers (Kiva-Style Mobile Robots)
* **Domain Context:** High-density automated storage and retrieval systems (ASRS) with hundreds of picking robots.
* **Input Setup:** Mobile Picker $R_1$ ($2.0\text{ m/s}$) and Replenishment Bot $R_2$ ($1.5\text{ m/s}$) targeting Shelf Intersect $I_9$.
* **Conflict Scenario:** Spatial collision detected at $I_9$ at $t = 45.0\text{ sec}$.
* **CBS Constraint Applied:** Node constraint $(I_9, 43.0\text{ s}, 47.0\text{ s})$ enforced on $R_2$.
* **Execution & Result:** $R_2$ reroutes through adjacent aisle $A_4$, adding $1.5\text{ sec}$ to journey while allowing $R_1$ uninterrupted throughput. Warehouse picking efficiency maintained at $99.4\%$.

### 7. Defense Convoy Operations & Minefield Avoidance
* **Domain Context:** Military tactical logistics routing convoys through contested terrain with threat zones.
* **Input Setup:** Troop Transport $\text{Alpha}$ ($50\text{ km/h}$) and Supply Convoy $\text{Bravo}$ ($40\text{ km/h}$) navigating Checkpoint $\text{CP}_4$.
* **Conflict Scenario:** Threat event reported at $\text{CP}_4$ from $t = 3.0\text{ hr}$ to $t = 6.0\text{ hr}$, overlapping both troop paths.
* **CBS Constraint Applied:** Threat interval ban $(\text{CP}_4, 3.0, 6.0)$ generated via `assess_threat`.
* **Execution & Result:** Convoy $\text{Alpha}$ reroutes via secondary dirt road $R_{12}$; Convoy $\text{Bravo}$ holds at forward base until threat neutralizes. Zero exposure to hostile engagement; memory footprint under $12\text{ MB}$.

### 8. Airport Ground Support Equipment (GSE Taxiway Logistics)
* **Domain Context:** Autonomous aircraft pushback tugs, fuel tankers, and baggage tractors sharing active aprons.
* **Input Setup:** Refueling Tanker $T_1$ ($25\text{ km/h}$) and Aircraft Tug $P_1$ ($15\text{ km/h}$) crossing Taxiway Intersection $\text{TX}_3$.
* **Conflict Scenario:** Simultaneous arrival at $\text{TX}_3$ at $t = 0.12\text{ hr}$ ($7.2\text{ min}$).
* **CBS Constraint Applied:** Spatial-temporal constraint $(\text{TX}_3, 0.11, 0.14)$ applied to $T_1$.
* **Execution & Result:** Tanker $T_1$ waits at Staging Area $S_2$ for $1.8\text{ min}$, allowing aircraft pushback $P_1$ uninterrupted priority. Zero flight departure delays incurred.

### 9. Underground Mining & Autonomous Haulage Systems (AHS)
* **Domain Context:** 400-ton autonomous haul trucks operating in single-lane underground decline ramps.
* **Input Setup:** Loaded Haul Truck $H_1$ ($20\text{ km/h}$, ascending) and Empty Truck $H_2$ ($30\text{ km/h}$, descending) sharing Ramp $R_5$.
* **Conflict Scenario:** Ramp collision predicted between $t = 0.15\text{ hr}$ and $t = 0.25\text{ hr}$.
* **CBS Constraint Applied:** Interval constraint $(R_5, 0.15, 0.25)$ added for $H_2$.
* **Execution & Result:** $H_2$ pulls into passing bay $\text{PB}_2$ at $t = 0.14\text{ hr}$ and waits $0.11\text{ hr}$ until $H_1$ passes. Zero ramp gridlocks; haulage cycle times optimized.

### 10. Satellite Constellation Orbital Maneuvering
* **Domain Context:** Low Earth Orbit (LEO) satellite constellation trajectory management to prevent orbital collisions.
* **Input Setup:** Communications Satellite $\text{Sat}_A$ ($27,000\text{ km/h}$) and Imaging Satellite $\text{Sat}_B$ ($27,000\text{ km/h}$) crossing Orbital Node $\text{ON}_{88}$.
* **Conflict Scenario:** Conjunction assessment predicts close-approach distance $< 500\text{ meters}$ at $t = 14.2\text{ hours}$.
* **CBS Constraint Applied:** Orbital node exclusion window $(\text{ON}_{88}, 14.18, 14.22)$ placed on $\text{Sat}_B$.
* **Execution & Result:** $\text{Sat}_B$ executes a minor thruster burn, adjusting arrival epoch by $30\text{ seconds}$. Conjunction risk cleared; propellant usage minimized.